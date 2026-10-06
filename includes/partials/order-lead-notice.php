<?php
/**
 * Reusable notice: orders need at least 24 hours notice.
 *
 * Set $order_lead_notice_variant to "compact" for a single short line.
 */
$order_lead_notice_variant = $order_lead_notice_variant ?? 'default';
$order_lead_notice_class = 'order-lead-notice';

if ($order_lead_notice_variant === 'compact') {
    $order_lead_notice_class .= ' order-lead-notice--compact';
}
?>
<aside class="<?= htmlspecialchars($order_lead_notice_class) ?>" role="note" aria-label="Order notice">
    <?php if ($order_lead_notice_variant === 'compact'): ?>
        <p class="order-lead-notice__text"><?= htmlspecialchars(get_order_lead_notice_short()) ?></p>
    <?php else: ?>
        <?php foreach (get_order_lead_notice_paragraphs() as $paragraph): ?>
            <p class="order-lead-notice__text"><?= htmlspecialchars($paragraph) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>
</aside>
