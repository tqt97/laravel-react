<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Listing\ListQuery;
use App\Support\Listing\ListQueryOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_sort_uses_the_default_sort_and_tie_breaker(): void
    {
        $oldest = User::factory()->create([
            'name' => 'Alpha',
            'created_at' => now()->subDay(),
        ]);
        $newest = User::factory()->create([
            'name' => 'Zulu',
            'created_at' => now(),
        ]);

        $paginator = app(ListQuery::class)->paginate(
            User::query(),
            new ListQueryOptions(
                sortableColumns: ['name' => 'name', 'created_at' => 'created_at'],
                sort: 'not-allowed',
                direction: 'asc',
                perPage: 15,
            ),
        );

        $this->assertSame([$oldest->id, $newest->id], $paginator->pluck('id')->all());
    }

    public function test_invalid_direction_falls_back_to_descending(): void
    {
        $oldest = User::factory()->create(['created_at' => now()->subDay()]);
        $newest = User::factory()->create(['created_at' => now()]);

        $paginator = app(ListQuery::class)->paginate(
            User::query(),
            new ListQueryOptions(
                sortableColumns: ['created_at' => 'created_at'],
                sort: 'created_at',
                direction: 'sideways',
                perPage: 15,
            ),
        );

        $this->assertSame([$newest->id, $oldest->id], $paginator->pluck('id')->all());
    }

    public function test_tie_breaker_makes_equal_sort_values_deterministic(): void
    {
        $first = User::factory()->create(['name' => 'Same']);
        $second = User::factory()->create(['name' => 'Same']);

        $paginator = app(ListQuery::class)->paginate(
            User::query(),
            new ListQueryOptions(
                sortableColumns: ['name' => 'name'],
                sort: 'name',
                direction: 'asc',
                perPage: 15,
            ),
        );

        $this->assertSame([$second->id, $first->id], $paginator->pluck('id')->all());
    }

    public function test_pagination_preserves_the_original_query_string(): void
    {
        User::factory()->count(2)->create();

        request()->query->set('search', 'alice');

        $paginator = app(ListQuery::class)->paginate(
            User::query(),
            new ListQueryOptions(
                sortableColumns: ['created_at' => 'created_at'],
                sort: 'created_at',
                direction: 'desc',
                perPage: 1,
            ),
        );

        $this->assertStringContainsString('search=alice', $paginator->url(2));
    }
}
