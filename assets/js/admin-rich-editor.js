/**
 * Eddilicious Foods — admin rich text editor (contenteditable)
 */
(function (window, document) {
    'use strict';

    const editors = new WeakMap();
    const boundRoots = new WeakSet();
    const BLOCK_TAGS = ['p', 'h1', 'h2', 'h3', 'h4', 'h5'];
    const BLOCK_SELECTOR = BLOCK_TAGS.join(',');
    const STYLE_OPTIONS = [
        { tag: 'p', label: 'Normal text' },
        { tag: 'h1', label: 'Heading 1' },
        { tag: 'h2', label: 'Heading 2' },
        { tag: 'h3', label: 'Heading 3' },
        { tag: 'h4', label: 'Heading 4' },
        { tag: 'h5', label: 'Heading 5' },
    ];

    function exec(command, value) {
        document.execCommand('styleWithCSS', false, 'false');
        document.execCommand(command, false, value || null);
    }

    function normalizeHtml(html) {
        return String(html || '')
            .replace(/<div>/gi, '<p>')
            .replace(/<\/div>/gi, '</p>')
            .replace(/<p><\/p>/gi, '')
            .trim();
    }

    function labelForTag(tag) {
        const match = STYLE_OPTIONS.find(function (option) {
            return option.tag === tag;
        });
        return match ? match.label : 'Normal text';
    }

    function getBlockParent(node, surface) {
        while (node && node !== surface) {
            if (node.nodeType === Node.ELEMENT_NODE) {
                const tag = node.tagName.toLowerCase();
                if (BLOCK_TAGS.indexOf(tag) !== -1) {
                    return node;
                }
            }
            node = node.parentNode;
        }

        return null;
    }

    function placeCaretIn(node, atEnd) {
        const selection = window.getSelection();
        if (!selection || !node) {
            return;
        }

        const range = document.createRange();
        range.selectNodeContents(node);
        range.collapse(!atEnd);
        selection.removeAllRanges();
        selection.addRange(range);
    }

    function ensureBlock(surface) {
        let block = surface.querySelector(BLOCK_SELECTOR);

        if (!block) {
            block = document.createElement('p');
            block.appendChild(document.createElement('br'));
            surface.innerHTML = '';
            surface.appendChild(block);
        }

        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0 || !surface.contains(selection.anchorNode)) {
            placeCaretIn(block, false);
        }

        return block;
    }

    function getCurrentBlockTag(state) {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            return 'p';
        }

        const block = getBlockParent(selection.anchorNode, state.surface);
        if (!block) {
            return 'p';
        }

        const tag = block.tagName.toLowerCase();
        return BLOCK_TAGS.indexOf(tag) !== -1 ? tag : 'p';
    }

    function applyBlockFormat(state, tag) {
        if (BLOCK_TAGS.indexOf(tag) === -1) {
            tag = 'p';
        }

        state.surface.focus();
        const block = ensureBlock(state.surface);
        let target = getBlockParent(window.getSelection().anchorNode, state.surface) || block;

        if (target.tagName.toLowerCase() === tag) {
            updateStyleUi(state, tag);
            return;
        }

        const next = document.createElement(tag);
        while (target.firstChild) {
            next.appendChild(target.firstChild);
        }

        if (!next.childNodes.length) {
            next.appendChild(document.createElement('br'));
        }

        target.parentNode.replaceChild(next, target);
        placeCaretIn(next, true);
        updateStyleUi(state, tag);
    }

    function updateStyleUi(state, tag) {
        const activeTag = tag || getCurrentBlockTag(state);

        if (state.styleLabel) {
            state.styleLabel.textContent = labelForTag(activeTag);
        }

        if (state.styleTrigger) {
            state.styleTrigger.setAttribute('aria-label', 'Text style: ' + labelForTag(activeTag));
        }

        if (state.styleOptions) {
            state.styleOptions.forEach(function (button) {
                const buttonTag = button.getAttribute('data-rich-block');
                const isActive = buttonTag === activeTag;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }
    }

    function closeStyleMenu(state) {
        if (!state.styleMenu) {
            return;
        }

        state.styleMenu.hidden = true;
        if (state.styleTrigger) {
            state.styleTrigger.setAttribute('aria-expanded', 'false');
        }
        state.root.classList.remove('is-style-open');
    }

    function openStyleMenu(state) {
        if (!state.styleMenu) {
            return;
        }

        updateStyleUi(state);
        state.styleMenu.hidden = false;
        if (state.styleTrigger) {
            state.styleTrigger.setAttribute('aria-expanded', 'true');
        }
        state.root.classList.add('is-style-open');
    }

    function syncInput(root) {
        const state = editors.get(root);
        if (!state) {
            return;
        }

        const html = normalizeHtml(state.surface.innerHTML);
        state.input.value = html;
        state.surface.classList.toggle('is-empty', state.surface.textContent.trim() === '');
    }

    function bindRoot(root) {
        if (!root || boundRoots.has(root)) {
            return root;
        }

        const surface = root.querySelector('[data-rich-editor-surface]');
        const input = root.querySelector('[data-rich-editor-input]');
        const styleTrigger = root.querySelector('[data-rich-style-trigger]');
        const styleMenu = root.querySelector('[data-rich-style-menu]');
        const styleLabel = root.querySelector('[data-rich-style-label]');
        const styleOptions = root.querySelectorAll('[data-rich-block]');

        if (!surface || !input) {
            return null;
        }

        try {
            document.execCommand('defaultParagraphSeparator', false, 'p');
        } catch (error) {
            // Ignore.
        }

        const state = {
            root: root,
            surface: surface,
            input: input,
            styleTrigger: styleTrigger,
            styleMenu: styleMenu,
            styleLabel: styleLabel,
            styleOptions: styleOptions,
        };

        editors.set(root, state);
        boundRoots.add(root);

        root.querySelectorAll('[data-rich-command]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                ensureBlock(surface);
                surface.focus();

                const command = button.getAttribute('data-rich-command');
                if (command) {
                    exec(command);
                }

                syncInput(root);
                updateStyleUi(state);
            });
        });

        if (styleTrigger && styleMenu) {
            styleTrigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (styleMenu.hidden) {
                    openStyleMenu(state);
                } else {
                    closeStyleMenu(state);
                }
            });

            styleOptions.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    const tag = button.getAttribute('data-rich-block') || 'p';
                    applyBlockFormat(state, tag);
                    syncInput(root);
                    closeStyleMenu(state);
                    surface.focus();
                });
            });

            document.addEventListener('click', function (event) {
                if (!root.classList.contains('is-style-open')) {
                    return;
                }

                if (root.contains(event.target)) {
                    return;
                }

                closeStyleMenu(state);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && root.classList.contains('is-style-open')) {
                    closeStyleMenu(state);
                    surface.focus();
                }
            });
        }

        surface.addEventListener('input', function () {
            syncInput(root);
            updateStyleUi(state);
        });

        surface.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                ensureBlock(surface);

                const block = getBlockParent(window.getSelection().anchorNode, surface);
                const heading = block && /^h[1-5]$/.test(block.tagName.toLowerCase());

                if (heading) {
                    const paragraph = document.createElement('p');
                    paragraph.appendChild(document.createElement('br'));
                    block.parentNode.insertBefore(paragraph, block.nextSibling);
                    placeCaretIn(paragraph, false);
                } else {
                    exec('insertParagraph');
                }

                syncInput(root);
                updateStyleUi(state);
            }
        });

        surface.addEventListener('keyup', function () {
            updateStyleUi(state);
        });

        surface.addEventListener('mouseup', function () {
            updateStyleUi(state);
        });

        surface.addEventListener('paste', function (event) {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text/plain');
            if (text) {
                exec('insertText', text);
            }
            syncInput(root);
            updateStyleUi(state);
        });

        surface.addEventListener('blur', function () {
            syncInput(root);
            closeStyleMenu(state);
        });

        surface.addEventListener('focus', function () {
            surface.classList.remove('is-empty');
            ensureBlock(surface);
            updateStyleUi(state);
        });

        syncInput(root);
        updateStyleUi(state, 'p');

        return root;
    }

    function init(container) {
        if (!container) {
            return null;
        }

        const existing = container.matches('[data-rich-editor-root]')
            ? container
            : container.querySelector('[data-rich-editor-root]');

        if (existing) {
            return bindRoot(existing);
        }

        return null;
    }

    function setHtml(root, html) {
        const state = editors.get(root);
        if (!state) {
            return;
        }

        const clean = normalizeHtml(html);
        state.surface.innerHTML = clean;
        syncInput(root);

        if (clean) {
            ensureBlock(state.surface);
        }

        updateStyleUi(state);
    }

    function getHtml(root) {
        const state = editors.get(root);
        if (!state) {
            return '';
        }

        syncInput(root);
        return state.input.value.trim();
    }

    function clear(root) {
        const state = editors.get(root);
        if (!state) {
            return;
        }

        state.surface.innerHTML = '';
        syncInput(root);
        updateStyleUi(state, 'p');
    }

    function bindAll() {
        document.querySelectorAll('[data-rich-editor-root]').forEach(function (root) {
            bindRoot(root);
        });
    }

    window.EddiliciousRichEditor = {
        bind: bindRoot,
        init: init,
        setHtml: setHtml,
        getHtml: getHtml,
        clear: clear,
        bindAll: bindAll,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindAll);
    } else {
        bindAll();
    }
})(window, document);
