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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('external_shop_id')->nullable()->after('code')->comment('Official Platform Store/Seller ID');
            $table->text('access_token')->nullable()->after('external_shop_id')->comment('OAuth Access Token');
            $table->text('refresh_token')->nullable()->after('access_token')->comment('OAuth Refresh Token');
            $table->timestamp('token_expires_at')->nullable()->after('refresh_token')->comment('Token expiration timestamp');
            $table->json('extra_credentials')->nullable()->after('token_expires_at')->comment('Platform-specific metadata (e.g. shop_cipher)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'external_shop_id',
                'access_token',
                'refresh_token',
                'token_expires_at',
                'extra_credentials',
            ]);
        });
    }
};
