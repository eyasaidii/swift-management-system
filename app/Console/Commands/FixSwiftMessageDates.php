<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSwiftMessageDates extends Command
{
    protected $signature = 'swift:fix-dates';
    protected $description = 'Corrige les dates NULL dans les messages SWIFT';

    public function handle()
    {
        $this->info('🔍 Recherche des messages sans date...');
        
        $count = DB::table('messages_swift')
            ->whereNull('CREATED_AT')
            ->count();
            
        if ($count === 0) {
            $this->info('✅ Aucun message sans date trouvé !');
            return 0;
        }
        
        $this->warn("⚠️  {$count} messages sans date trouvés.");
        
        if ($this->confirm('Voulez-vous leur attribuer la date actuelle ?')) {
            $updated = DB::table('messages_swift')
                ->whereNull('CREATED_AT')
                ->update(['CREATED_AT' => now()]);
                
            $this->info("✅ {$updated} messages mis à jour avec la date actuelle !");
        }
        
        return 0;
    }
}