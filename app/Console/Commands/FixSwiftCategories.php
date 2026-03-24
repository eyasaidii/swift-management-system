<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MessageSwift;

class FixSwiftCategories extends Command
{
    protected $signature = 'swift:fix-categories';
    protected $description = 'Corrige les catégories des messages SWIFT';

    public function handle()
    {
        $this->info('🔍 Correction des catégories des messages SWIFT...');
        
        $messages = MessageSwift::all();
        $count = 0;
        
        foreach ($messages as $msg) {
            $type = $msg->TYPE_MESSAGE;
            $ancienne = $msg->CATEGORIE;
            
            if (str_starts_with($type, 'pacs')) {
                $categorie = 'PACS';
            } elseif (str_starts_with($type, 'camt')) {
                $categorie = 'CAMT';
            } elseif (str_starts_with($type, 'MT1')) {
                $categorie = '1';
            } elseif (str_starts_with($type, 'MT2')) {
                $categorie = '2';
            } elseif (str_starts_with($type, 'MT3')) {
                $categorie = '3';
            } elseif (str_starts_with($type, 'MT4')) {
                $categorie = '4';
            } elseif (str_starts_with($type, 'MT5')) {
                $categorie = '5';
            } elseif (str_starts_with($type, 'MT7')) {
                $categorie = '7';
            } elseif (str_starts_with($type, 'MT9')) {
                $categorie = '9';
            } else {
                $categorie = 'AUTRE';
            }
            
            if ($ancienne !== $categorie) {
                $msg->CATEGORIE = $categorie;
                $msg->save();
                $count++;
                $this->line("   {$msg->REFERENCE} : {$type} → {$categorie}");
            }
        }
        
        $this->info("✅ {$count} messages mis à jour !");
        return 0;
    }
}