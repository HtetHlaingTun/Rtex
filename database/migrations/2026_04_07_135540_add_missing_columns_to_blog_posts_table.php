<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('blog_posts', 'title')) {
                $table->string('title')->after('id');
            }
            if (!Schema::hasColumn('blog_posts', 'slug')) {
                $table->string('slug')->unique()->after('title');
            }
            if (!Schema::hasColumn('blog_posts', 'excerpt')) {
                $table->text('excerpt')->after('slug');
            }
            if (!Schema::hasColumn('blog_posts', 'content')) {
                $table->longText('content')->after('excerpt');
            }
            if (!Schema::hasColumn('blog_posts', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('content');
            }
            if (!Schema::hasColumn('blog_posts', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('blog_posts', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('blog_posts', 'views')) {
                $table->integer('views')->default(0)->after('meta_description');
            }
            if (!Schema::hasColumn('blog_posts', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('views');
            }
            if (!Schema::hasColumn('blog_posts', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
        });
    }

    public function down()
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $columns = [
                'title',
                'slug',
                'excerpt',
                'content',
                'featured_image',
                'meta_title',
                'meta_description',
                'views',
                'is_published',
                'published_at'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('blog_posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
