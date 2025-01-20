<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void  
       {  
           Schema::create('story_images', function (Blueprint $table) {  
               $table->id();  
               $table->unsignedBigInteger('story_id');  
               $table->string('image_path');  
               $table->timestamps();  
  
               $table->foreign('story_id')->references('id')->on('stories')->onDelete('cascade');  
           });  
       }  

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_images');
    }
};
