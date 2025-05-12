<?php

use App\Models\Document;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('storageFilename')->nullable();
            $table->string('originalFilename')->nullable();
            $table->string('mimetype')->nullable();
            $table->string('token');
            $table->string('title');
            $table->dateTime('contentModificationDate')->nullable();
            $table->boolean('isFolder');
            $table->boolean('isSensible');
        });

        Schema::create('document_to_document', function (Blueprint $table) {
            $table->foreignIdFor(Document::class, 'parentDocumentId')->constrained()->onDelete('cascade');
            $table->foreignIdFor(Document::class, 'childDocumentId')->constrained()->onDelete('cascade');
            $table->primary(['parentDocumentId', 'childDocumentId']);
        });

        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->string('ipAddress');
            $table->dateTime('downloadDate');
            $table->string('userAgent')->nullable();
            $table->string('storageFilename')->nullable();
            $table->string('originalFilename')->nullable();
            $table->string('mimetype')->nullable();
            $table->string('token');
            $table->text('infos');
            $table->string('title');
            $table->dateTime('contentModificationDate')->nullable();
            $table->boolean('isFolder');
        });


        $testPdfId =  DB::table('documents')->insertGetId([
            'storageFilename' => 'test-pdf.pdf',
            'originalFilename' => 'test-pdf.pdf',
            'mimetype' => 'text/plain',
            'token' => 'test-pdf',
            'title' => 'A PDF',
            'contentModificationDate' => '2025-05-09 13:13:16',
            'isFolder' => false,
            'isSensible' => false,
        ]);
        DB::table('documents')->insert([
            'storageFilename' => 'test-rib.txt',
            'originalFilename' => 'test-rib.txt',
            'mimetype' => 'text/plain',
            'token' => 'test-rib',
            'title' => 'A RIB',
            'contentModificationDate' => '2025-05-09 13:13:16',
            'isFolder' => false,
            'isSensible' => true,
        ]);
        $testImageId = DB::table('documents')->insertGetId([
            'storageFilename' => 'test-image.jpg',
            'originalFilename' => 'test-image.jpg',
            'mimetype' => null,
            'token' => 'test-image',
            'title' => 'An Image',
            'contentModificationDate' => '2025-05-09 13:13:16',
            'isFolder' => false,
            'isSensible' => false,
        ]);
        $testFolderId = DB::table('documents')->insertGetId([
            'storageFilename' => null,
            'originalFilename' => null,
            'mimetype' => null,
            'token' => 'test-folder',
            'title' => 'A Folder',
            'contentModificationDate' => null,
            'isFolder' => true,
            'isSensible' => false,
        ]);
        DB::table('document_to_document')->insert([
            'parentDocumentId' => $testFolderId,
            'childDocumentId' => $testPdfId,
        ]);
        DB::table('document_to_document')->insert([
            'parentDocumentId' => $testFolderId,
            'childDocumentId' => $testImageId,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
