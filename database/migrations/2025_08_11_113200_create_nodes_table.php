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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Node name
            $table->enum('type', ['Corporation', 'Building', 'Property', 'Tenancy Period', 'Tenant']); // Node types
            $table->foreignId('parent_id')->nullable()->constrained('nodes')->onDelete('cascade'); // Parent-relationship
            $table->string('relationship_to_parent')->nullable();
            $table->integer('height')->default(0); // Height of the node

            // Based on type
            $table->string('zip-code')->nullable(); // Buildings should have an extra field specifying the zip code they are located in.
            $table->decimal('monthly_rent', 10, 2)->nullable(); //Properties should have an extra field specifying the monthly rent of the property.
            $table->boolean('tenancy_active')->nullable(); //Tenancy periods should have an extra field determining whether they are active or not.
            $table->date('moved_in_at')->nullable(); // Tenants should have an extra field showing when they moved in.

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['type', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
