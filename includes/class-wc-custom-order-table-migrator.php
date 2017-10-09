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

    public function migrate($orders_batch, $orders_page)
    {
        global $wpdb;

        $order_table = wc_custom_order_table()->get_table_name();

        $order_count = $this->count();
        $total_pages = ceil($order_count / $orders_batch);

        $orders_sql = $wpdb->prepare("
          SELECT ID FROM {$wpdb->posts}
          WHERE post_type IN ('%s')
          ORDER BY post_date DESC
        ", implode(',', wc_get_order_types('reports')));
        $batches_processed = 0;

        for ($page = $orders_page; $page <= $total_pages; $page++) {
            $offset = ($page * $orders_batch) - $orders_batch;
            $sql = $wpdb->prepare($orders_sql . ' LIMIT %d OFFSET %d', $orders_batch, max($offset, 0));
            $orders = $wpdb->get_col($sql);

            foreach ($orders as $order) {
                // Accessing the order via wc_get_order will automatically migrate the order to the custom table.
                wc_get_order($order);

                do_action('wc_custom_order_table_migrate_tick');
            }

            $batches_processed++;
        }

        return $batches_processed;
    }
}
