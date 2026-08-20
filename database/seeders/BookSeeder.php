<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $books = [
            [
                'user_id' => User::first()->id,
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'description' => '名前のない一匹の猫の視点から、人間社会をユーモアたっぷりに描いた夏目漱石の代表作。明治時代の風刺や人間観察を楽しみながら、文学の魅力に触れられる長編小説です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
                'genres' => ['小説'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_date' => '1936-10-01',
                'description' => '人間関係を円滑にし、相手の心を動かすための原則を豊富な実例とともに解説した名著。仕事や家庭、あらゆる場面で役立つコミュニケーションの基本が学べます。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell ',
                'isbn' => '9784873115658',
                'published_date' => '2012-06-23',
                'description' => '読みやすく保守しやすいコードを書くための考え方やテクニックを具体例とともに解説。初心者から実務経験者まで役立つ、ソフトウェア開発者必携の一冊です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
                'genres' => ['技術書'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_date' => '2013-08-30',
                'description' => '成功を一時的なテクニックではなく、人格や価値観に基づく習慣から築く方法を紹介。人生や仕事をより良い方向へ導くための普遍的な原則が学べます。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_date' => '1906-04-01',
                'description' => '正義感あふれる青年教師「坊っちゃん」が地方の中学校で巻き起こす騒動を描いた名作。痛快なストーリーと個性豊かな登場人物が魅力の青春文学です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
                'genres' => ['小説'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_date' => '2016-09-08',
                'description' => '人類の誕生から現代までの歴史を、生物学・歴史学・経済学など多角的な視点で解説。人類が文明を築いた理由や社会の仕組みを新たな視点で理解できます。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
                'genres' => ['歴史', '科学'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_date' => '2017-12-18',
                'description' => '保守性が高く、美しく読みやすいコードを書くための原則を実践的に解説。リファクタリングや設計思想も学べる、エンジニアに長く支持される技術書です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
                'genres' => ['技術書'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => '嫌われる勇気 ',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_date' => '2013-12-13',
                'description' => 'アドラー心理学を対話形式でわかりやすく解説。「他人の期待ではなく、自分らしく生きる」ための考え方を学び、人生や人間関係を見直すきっかけを与えてくれます。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
                'genres' => ['自己啓発'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_date' => '2015-03-11',
                'description' => 'お笑い芸人として生きる若者たちの葛藤や友情、夢を繊細に描いた芥川賞受賞作。笑いの世界を通して、人間の才能や生き方について深く考えさせられる小説です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
                'genres' => ['小説'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_date' => '2019-01-11',
                'description' => '思い込みや偏見に左右されず、データに基づいて世界を正しく見るための考え方を紹介。統計をもとに現代社会を読み解く力が身につく世界的ベストセラーです。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'user_id' => User::first()->id,
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_date' => '2007-01-18',
                'description' => '海上コンテナの誕生が世界経済や物流をどのように変えたのかを描くノンフィクション。現代のグローバル社会を支える物流革命の歴史をわかりやすく解説しています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        foreach ($books as $data) {
            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'user_id' => $data['user_id'],
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'isbn' => $data['isbn'],
                    'published_date' => $data['published_date'],
                    'description' => $data['description'],
                    'image_url' => $data['image_url'],
                ]);

            $genreId = array_map(fn ($genre) => Genre::where('name', $genre)->first()->id, $data['genres']);
            $book->genres()->sync($genreId);
        }
    }
}
