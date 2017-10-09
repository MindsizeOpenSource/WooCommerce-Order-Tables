<?php

class WC_Custom_Order_Table_Migrator {

    /**
     * @return int
     */
    public function count()
    {
        global $wpdb;

        $order_table = wc_custom_order_table()->get_table_name();
        $order_types = $this->get_escaped_order_types();

        $order_count = $wpdb->get_var("
            SELECT COUNT(1)
            FROM {$wpdb->posts} p
            LEFT JOIN {$order_table} o ON p.ID = o.order_id
            WHERE p.post_type IN ({$order_types})
            AND o.order_id IS NULL
            ORDER BY p.post_date DESC
        ");

        return $order_count;
    }

    public function get_escaped_order_types()
    {
        return implode(',', array_map(
            array($this, 'escape_for_in_array_query'),
            wc_get_order_types('reports')
        ));
    }

    /**
     * Manually escape a value so it can be used as a comma separated set of
     * values for usage inside a IN query structure
     * @param string $value
     * @return string
     */
    public function escape_for_in_array_query($value)
    {
        return "'" . esc_sql($value) . "'";
    }

    public function migrate($orders_batch, $orders_page)
    {
        global $wpdb;

        $order_table = wc_custom_order_table()->get_table_name();

        $order_count = $this->count();
        $total_pages = ceil($order_count / $orders_batch);

        $order_types = $this->get_escaped_order_types();

        $orders_sql = "
          SELECT ID FROM {$wpdb->posts}
          WHERE post_type IN ({$order_types})
          ORDER BY post_date DESC
        ";
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
