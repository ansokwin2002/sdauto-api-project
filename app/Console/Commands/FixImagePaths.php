<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixImagePaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fix-image-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes the escaped forward slashes in product image paths.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to fix product image paths...');

        $products = Product::all();
        $progressBar = $this->output->createProgressBar($products->count());
        $fixedCount = 0;

        foreach ($products as $product) {
            $rawImages = $product->getRawOriginal('images');
            if ($rawImages && strpos($rawImages, '\\/') !== false) {
                $product->update(['images' => $product->images]);
                $fixedCount++;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nDone. Fixed image paths for {$fixedCount} products.");

        return 0;
    }
}
