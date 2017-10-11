<?php

/**
 * CLI Tool for migrating order data to/from custom table.
 *
 * @version  1.0.0
 * @category Class
 */
class WC_Custom_Order_Table_CLI extends WP_CLI_Command
{
    private $progress;
    private $migrator;
    private $count;

    public function __construct() {
        $this->migrator = new WC_Custom_Order_Table_Migrator();
    }

    /**
     * Count how many orders have yet to be migrated.
     *
     * ## EXAMPLES
     *
     *     wp wc-order-table count
     *
     */
    public function count() {
        $order_count = $this->migrator->count();

        WP_CLI::log( sprintf( __( '%d orders to be migrated.', 'wc-custom-order-table' ), $order_count ) );
    }

    /**
     * Migrate order data to the custom order table.
     *
     * ## OPTIONS
     *
     * [--batch=<batch>]
     * : The number of orders to process.
     * ---
     * default: 1000
     * ---
     *
     * [--page=<page>]
     * : The page to start from.
     * ---
     * default: 1
     * ---
     *
     * ## EXAMPLES
     *
     *     wp wc-order-table migrate --batch=100 --page=1
     *
     */
    public function migrate($args, $assoc_args)
    {
        add_action('wc_custom_order_table_migrate_tick', array($this, 'tick'));

        $orders_batch = isset($assoc_args['batch']) ? absint($assoc_args['batch']) : 1000;
        $orders_page = isset($assoc_args['page']) ? absint($assoc_args['page']) : 1;

        $order_count = $this->migrator->count();

        $this->progress = \WP_CLI\Utils\make_progress_bar('Order Data Migration', $order_count);

        $batches_processed = $this->migrator->migrate($orders_batch, $orders_page);

        $this->progress->finish();

        WP_CLI::log(sprintf(__('%d orders processed in %d batches.', 'wc-custom-order-table'), $order_count, $batches_processed));
    }

    public function tick()
    {
        $this->progress->tick();
    }

    /**
     * Backfill order meta data into postmeta.
     *
     * ## OPTIONS
     *
     * [--batch=<batch>]
     * : The number of orders to process.
     * ---
     * default: 1000
     * ---
     *
     * [--page=<page>]
     * : The page to start from.
     * ---
     * default: 1
     * ---
     *
     * ## EXAMPLES
     *
     *     wp wc-order-table backfill --batch=100 --page=1
     *
     */
    public function backfill($args, $assoc_args)
    {
        add_action('wc_custom_order_table_backfill_tick', array($this, 'tick'));

        $orders_batch = isset($assoc_args['batch']) ? absint($assoc_args['batch']) : 1000;
        $orders_page = isset($assoc_args['page']) ? absint($assoc_args['page']) : 1;
        $order_count = $this->migrator->count_custom_table();

        WP_CLI::log( sprintf( __( '%d orders to be backfilled.', 'wc-custom-order-table' ), $order_count ) );

        $this->progress = \WP_CLI\Utils\make_progress_bar('Order Data Migration', $order_count);

        $batches_processed = $this->migrator->backfill($orders_batch, $orders_page);

        $this->progress->finish();

        WP_CLI::log(sprintf(__('%d orders processed in %d batches.', 'wc-custom-order-table'), $order_count, $batches_processed));
    }
}
