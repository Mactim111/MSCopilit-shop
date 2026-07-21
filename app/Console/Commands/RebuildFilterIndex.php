<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use App\Observers\ProductVariantObserver;
use Illuminate\Console\Command;

class RebuildFilterIndex extends Command
{
    protected $signature   = 'catalog:rebuild-filter-index {--chunk=200}';
    protected $description = 'Пересобрать индекс фильтрации по всем вариантам товаров.';

    public function handle(ProductVariantObserver $observer): int
    {
        $total = ProductVariant::count();
        $this->info("Пересборка индекса: {$total} вариантов...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        ProductVariant::with(['product', 'propertyOptions.property'])
            ->chunkById((int) $this->option('chunk'), function ($variants) use ($observer, $bar) {
                foreach ($variants as $variant) {
                    $observer->saved($variant);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Готово.');
        return self::SUCCESS;
    }
}
