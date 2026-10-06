<?php
/**
 * Admin rich text editor markup — always rendered in HTML so the field is visible.
 *
 * @var string $rich_editor_placeholder
 * @var string $rich_editor_id
 */
$rich_editor_placeholder = $rich_editor_placeholder ?? 'Write your meal description here…';
$rich_editor_id = $rich_editor_id ?? '';
$idAttr = $rich_editor_id !== '' ? ' id="' . htmlspecialchars($rich_editor_id, ENT_QUOTES, 'UTF-8') . '"' : '';

$style_options = [
    ['tag' => 'p', 'label' => 'Normal text'],
    ['tag' => 'h1', 'label' => 'Heading 1'],
    ['tag' => 'h2', 'label' => 'Heading 2'],
    ['tag' => 'h3', 'label' => 'Heading 3'],
    ['tag' => 'h4', 'label' => 'Heading 4'],
    ['tag' => 'h5', 'label' => 'Heading 5'],
];
?>
<div class="rich-editor" data-rich-editor-root<?= $idAttr ?>>
    <div class="rich-editor__toolbar" role="toolbar" aria-label="Formatting">
        <div class="rich-editor__group" role="group" aria-label="Text style">
            <button type="button" class="rich-editor__btn" data-rich-command="bold" aria-label="Bold" title="Bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 4h8a4 4 0 0 1 0 8H6z"/><path d="M6 12h9a4 4 0 0 1 0 8H6z"/></svg>
            </button>
            <button type="button" class="rich-editor__btn" data-rich-command="italic" aria-label="Italic" title="Italic">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg>
            </button>
        </div>

        <span class="rich-editor__divider" aria-hidden="true"></span>

        <div class="rich-editor__group" role="group" aria-label="Lists">
            <button type="button" class="rich-editor__btn" data-rich-command="insertUnorderedList" aria-label="Bullet list" title="Bullet list">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/></svg>
            </button>
            <button type="button" class="rich-editor__btn" data-rich-command="insertOrderedList" aria-label="Numbered list" title="Numbered list">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="10" x2="21" y1="6" y2="6"/><line x1="10" x2="21" y1="12" y2="12"/><line x1="10" x2="21" y1="18" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M5 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
            </button>
        </div>

        <span class="rich-editor__divider" aria-hidden="true"></span>

        <div class="rich-editor__style" data-rich-style-wrap>
            <button
                type="button"
                class="rich-editor__style-trigger"
                data-rich-style-trigger
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-label="Text style: Normal text"
            >
                <span class="rich-editor__style-value" data-rich-style-label>Normal text</span>
                <svg class="rich-editor__style-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="rich-editor__style-menu" data-rich-style-menu hidden role="listbox" aria-label="Choose text style">
                <?php foreach ($style_options as $option): ?>
                    <button
                        type="button"
                        class="rich-editor__style-option"
                        role="option"
                        data-rich-block="<?= htmlspecialchars($option['tag'], ENT_QUOTES, 'UTF-8') ?>"
                        aria-selected="<?= $option['tag'] === 'p' ? 'true' : 'false' ?>"
                    >
                        <span class="rich-editor__style-option-tag"><?= htmlspecialchars(strtoupper($option['tag'] === 'p' ? 'P' : $option['tag']), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="rich-editor__style-option-label"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="rich-editor__body">
        <div
            class="rich-editor__surface"
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
            spellcheck="true"
            data-rich-editor-surface
            data-placeholder="<?= htmlspecialchars($rich_editor_placeholder, ENT_QUOTES, 'UTF-8') ?>"
        ></div>
    </div>

    <input type="hidden" data-rich-editor-input>
</div>
