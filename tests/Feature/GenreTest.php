<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    // ============ index ============

    /** ジャンル一覧が、書籍数付き・名前昇順で表示される(認証済み) */
    public function test_authenticated_user_can_view_genre_index(): void
    {
        $user = User::factory()->create();
        $genreB = Genre::factory()->create(['name' => 'ミステリー']);
        $genreA = Genre::factory()->create(['name' => 'エッセイ']);
        Book::factory()->count(2)->create()->each(fn ($book) => $book->genres()->attach($genreA->id));

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);
        $genres = $response->viewData('genres');
        $this->assertEquals($genreA->id, $genres->first()->id);
        $this->assertEquals(2, $genres->first()->books_count);
    }

    /** 未ログインユーザーはジャンル一覧にアクセスできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_view_genre_index(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    // ============ show ============

    /** ジャンル詳細で、紐づく書籍がページネーション表示される(認証済み) */
    public function test_authenticated_user_can_view_genre_show(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Book::factory()->count(12)->create()->each(fn ($book) => $book->genres()->attach($genre->id));

        $response = $this->actingAs($user)->get("/genres/{$genre->id}");

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10;
        });
    }

    /** 未ログインユーザーはジャンル詳細にアクセスできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_view_genre_show(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get("/genres/{$genre->id}");

        $response->assertRedirect('/login');
    }

    // ============ store ============

    /** 正常系:認証済みユーザーがジャンルを登録できる */
    public function test_authenticated_user_can_store_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', ['name' => 'SF']);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを登録しました');
        $this->assertDatabaseHas('genres', ['name' => 'SF']);
    }

    /** 未ログインユーザーはジャンルを登録できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_store_genre(): void
    {
        $response = $this->post('/genres', ['name' => 'SF']);

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('genres', 0);
    }

    /** 異常系:ジャンル名が未入力 */
    public function test_store_fails_when_name_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', ['name' => '']);

        $response->assertSessionHasErrors('name');
    }

    /** 異常系:ジャンル名が21文字以上 */
    public function test_store_fails_when_name_exceeds_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', ['name' => str_repeat('あ', 21)]);

        $response->assertSessionHasErrors('name');
    }

    /** 境界値:ジャンル名がちょうど20文字 → 登録できる */
    public function test_store_succeeds_when_name_is_exactly_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', ['name' => str_repeat('あ', 20)]);

        $response->assertSessionDoesntHaveErrors('name');
        $this->assertDatabaseCount('genres', 1);
    }

    /** 異常系:ジャンル名が重複している */
    public function test_store_fails_when_name_already_exists(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => 'SF']);

        $response = $this->actingAs($user)->post('/genres', ['name' => 'SF']);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('genres', 1);
    }

    // ============ update ============

    /** 正常系:ログイン済みなら誰でもジャンルを更新できる(所有者制限なし) */
    public function test_any_authenticated_user_can_update_genre(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '元の名前']);

        $response = $this->actingAs($otherUser)->put("/genres/{$genre->id}", ['name' => '更新後の名前']);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを更新しました');
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '更新後の名前']);
    }

    /** 未ログインユーザーはジャンルを更新できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_update_genre(): void
    {
        $genre = Genre::factory()->create(['name' => '元の名前']);

        $response = $this->put("/genres/{$genre->id}", ['name' => '更新後の名前']);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '元の名前']);
    }

    /** 正常系:名前を変更せずに更新しても、unique制約でエラーにならない(自身は除外される) */
    public function test_update_succeeds_when_keeping_same_name(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'SF']);

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", ['name' => 'SF']);

        $response->assertSessionDoesntHaveErrors('name');
    }

    /** 異常系:他のジャンルが使っている名前には変更できない */
    public function test_update_fails_when_name_belongs_to_another_genre(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => 'ミステリー']);
        $genre = Genre::factory()->create(['name' => 'SF']);

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", ['name' => 'ミステリー']);

        $response->assertSessionHasErrors('name');
    }

    /** 異常系:ジャンル名が未入力 */
    public function test_update_fails_when_name_is_missing(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", ['name' => '']);

        $response->assertSessionHasErrors('name');
    }

    // ============ destroy ============

    /** 正常系:紐づく書籍が無いジャンルは削除できる */
    public function test_genre_without_books_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを削除しました');
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    /** 異常系:紐づく書籍があるジャンルは削除できない */
    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertSessionHas('error', '登録中の書籍があります。');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /** 未ログインユーザーはジャンルを削除できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_delete_genre(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->delete("/genres/{$genre->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(store) */
    public function test_store_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();

        Genre::creating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->post('/genres', ['name' => 'SF']);

        $response->assertSessionHas('error', 'ジャンルの登録に失敗しました');
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(update) */
    public function test_update_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Genre::updating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", ['name' => '更新名']);

        $response->assertSessionHas('error', 'ジャンルの更新に失敗しました');
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(destroy) */
    public function test_destroy_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Genre::deleting(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertSessionHas('error', 'ジャンルの削除に失敗しました');
    }

    /** 認証済みユーザーはジャンル登録画面を表示できる */
    public function test_authenticated_user_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres/create');

        $response->assertStatus(200);
        $response->assertViewIs('genres.create');
    }

    /** 認証済みユーザーはジャンル編集画面を表示できる */
    public function test_authenticated_user_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get("/genres/{$genre->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('genres.edit');
    }
}
