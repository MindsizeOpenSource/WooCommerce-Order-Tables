<?php

class WC_Custom_Order_Table_Migrator {

    /**
     * @return int
     */
    public function count()
    {
        global $wpdb;

        $order_table = wc_custom_order_table()->get_table_name();

        $order_count = $wpdb->get_var( $wpdb->prepare("
            SELECT COUNT(1)
            FROM {$wpdb->posts} p
            LEFT JOIN {$order_table} o ON p.ID = o.order_id
            WHERE p.post_type IN ('%s')
            AND o.order_id IS NULL
            ORDER BY p.post_date DESC
        ", implode(',', wc_get_order_types('reports'))));

        return $order_count;
    }
}
