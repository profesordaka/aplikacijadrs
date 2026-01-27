<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rekonstruiši sve preview URL-e sa ispravnim cloud_name
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $transformations = 'fl_page.1,w_400,h_500,c_fill,f_jpg,q_auto:best';
        
        // Uzmi sve fakultete koji imaju uputstvo_file
        $fakulteti = DB::table('fakulteti')
            ->whereNotNull('uputstvo_file')
            ->get();
        
        foreach ($fakulteti as $fakultet) {
            $pdfUrl = $fakultet->uputstvo_file;
            $previewUrl = "https://res.cloudinary.com/{$cloudName}/image/fetch/{$transformations}/" . base64_encode($pdfUrl);
            
            DB::table('fakulteti')
                ->where('id', $fakultet->id)
                ->update(['uputstvo_preview' => $previewUrl]);
        }
    }

    public function down(): void
    {
        // Null-uj sve preview URL-e
        DB::table('fakulteti')->update(['uputstvo_preview' => null]);
    }
};
