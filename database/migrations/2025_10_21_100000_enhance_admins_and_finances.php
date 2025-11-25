<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if packages table exists and add columns if needed
        if (Schema::hasTable('packages')) {
            // First, gather columns that need to be added
            $columnsToAdd = [];

            if (!Schema::hasColumn('packages', 'listing_limit')) {
                $columnsToAdd[] = 'listing_limit';
            }
            if (!Schema::hasColumn('packages', 'cards_limit')) {
                $columnsToAdd[] = 'cards_limit';
            }
            if (!Schema::hasColumn('packages', 'tenders_limit')) {
                $columnsToAdd[] = 'tenders_limit';
            }
            if (!Schema::hasColumn('packages', 'auctions_limit')) {
                $columnsToAdd[] = 'auctions_limit';
            }
            if (!Schema::hasColumn('packages', 'photos_limit')) {
                $columnsToAdd[] = 'photos_limit';
            }
            if (!Schema::hasColumn('packages', 'description_chars_limit')) {
                $columnsToAdd[] = 'description_chars_limit';
            }
            if (!Schema::hasColumn('packages', 'contacts_visible')) {
                $columnsToAdd[] = 'contacts_visible';
            }
            if (!Schema::hasColumn('packages', 'is_vip')) {
                $columnsToAdd[] = 'is_vip';
            }
            if (!Schema::hasColumn('packages', 'vip_level')) {
                $columnsToAdd[] = 'vip_level';
            }

            // If there are columns to add, modify the table
            if (!empty($columnsToAdd)) {
                Schema::table('packages', function (Blueprint $table) use ($columnsToAdd) {
                    $previousColumn = 'currency';

                    if (in_array('listing_limit', $columnsToAdd)) {
                        $table->integer('listing_limit')->default(0)->after($previousColumn);
                        $previousColumn = 'listing_limit';
                    }
                    if (in_array('cards_limit', $columnsToAdd)) {
                        $table->integer('cards_limit')->default(0)->after($previousColumn);
                        $previousColumn = 'cards_limit';
                    }
                    if (in_array('tenders_limit', $columnsToAdd)) {
                        $table->integer('tenders_limit')->default(0)->after($previousColumn);
                        $previousColumn = 'tenders_limit';
                    }
                    if (in_array('auctions_limit', $columnsToAdd)) {
                        $table->integer('auctions_limit')->default(0)->after($previousColumn);
                        $previousColumn = 'auctions_limit';
                    }
                    if (in_array('photos_limit', $columnsToAdd)) {
                        $table->integer('photos_limit')->default(15)->after($previousColumn);
                        $previousColumn = 'photos_limit';
                    }
                    if (in_array('description_chars_limit', $columnsToAdd)) {
                        $table->integer('description_chars_limit')->default(350)->after($previousColumn);
                        $previousColumn = 'description_chars_limit';
                    }
                    if (in_array('contacts_visible', $columnsToAdd)) {
                        $table->boolean('contacts_visible')->default(true)->after($previousColumn);
                        $previousColumn = 'contacts_visible';
                    }
                    if (in_array('is_vip', $columnsToAdd)) {
                        $table->boolean('is_vip')->default(false)->after($previousColumn);
                        $previousColumn = 'is_vip';
                    }
                    if (in_array('vip_level', $columnsToAdd)) {
                        $table->integer('vip_level')->nullable()->after($previousColumn);
                    }
                });
            }
        }

        // Check if admin_action_logs table exists before attempting to modify it
        if (Schema::hasTable('admin_action_logs')) {
            // Create admin actions log table (enhanced)
            Schema::table('admin_action_logs', function (Blueprint $table) {
                $table->string('action_type')->after('action');
                // created_user, approved_doc, deleted_card, changed_role, etc.
                $table->unsignedBigInteger('target_id')->nullable()->after('action_type');
                $table->string('target_type')->nullable()->after('target_id'); // User, Listing, etc.
                $table->text('comment')->nullable()->after('description');
            });
        }

        // Check if user_packages table exists before attempting to modify it
        if (Schema::hasTable('user_packages')) {
            // Enhance user_packages table
            Schema::table('user_packages', function (Blueprint $table) {
                if (!Schema::hasColumn('user_packages', 'cards_used')) {
                    $table->integer('cards_used')->default(0)->after('listings_remaining');
                }
                if (!Schema::hasColumn('user_packages', 'tenders_used')) {
                    $table->integer('tenders_used')->default(0)->after('cards_used');
                }
                if (!Schema::hasColumn('user_packages', 'auctions_used')) {
                    $table->integer('auctions_used')->default(0)->after('tenders_used');
                }
            });
        }

        // Create promo codes table if it doesn't exist
        if (!Schema::hasTable('promo_codes')) {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->enum('type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('value', 10, 2); // 10% or $10
                $table->integer('max_uses')->nullable();
                $table->integer('times_used')->default(0);
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Create virtual balance table for demo accounts if it doesn't exist
        if (!Schema::hasTable('virtual_balances')) {
            Schema::create('virtual_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->text('notes')->nullable();
                $table->foreignId('granted_by')->nullable()->references('id')->on('admins');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // Create payment methods table if it doesn't exist
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Card, Bank Transfer, Crypto, etc.
                $table->string('type'); // card, bank, crypto, virtual
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable(); // Provider-specific settings
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Create package features table if it doesn't exist
        if (!Schema::hasTable('package_features')) {
            Schema::create('package_features', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained()->onDelete('cascade');
                $table->string('feature_name');
                $table->string('feature_value');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraints
        if (Schema::hasTable('package_features')) {
            Schema::dropIfExists('package_features');
        }

        if (Schema::hasTable('payment_methods')) {
            Schema::dropIfExists('payment_methods');
        }

        if (Schema::hasTable('virtual_balances')) {
            Schema::dropIfExists('virtual_balances');
        }

        if (Schema::hasTable('promo_codes')) {
            Schema::dropIfExists('promo_codes');
        }

        // Remove columns from admin_action_logs if table exists
        if (Schema::hasTable('admin_action_logs')) {
            Schema::table('admin_action_logs', function (Blueprint $table) {
                $columns = ['action_type', 'target_id', 'target_type', 'comment'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('admin_action_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Remove columns from user_packages if table exists
        if (Schema::hasTable('user_packages')) {
            Schema::table('user_packages', function (Blueprint $table) {
                $columns = ['cards_used', 'tenders_used', 'auctions_used'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('user_packages', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Remove columns from packages if table exists
        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                $columns = [
                    'listing_limit', 'cards_limit', 'tenders_limit', 'auctions_limit',
                    'photos_limit', 'description_chars_limit',
                    'contacts_visible', 'is_vip', 'vip_level'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('packages', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
