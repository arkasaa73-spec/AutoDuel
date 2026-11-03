<?php

namespace App\Console\Commands;

use App\Models\Car;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class FetchCarPhotos extends Command
{
    protected $signature = 'cars:photos
                            {--car= : only this car id}
                            {--query= : custom search text (use together with --car)}
                            {--file= : exact Commons file name, e.g. "Porsche 992 front.jpg" (use together with --car)}
                            {--pick=1 : take the Nth best candidate instead of the first}
                            {--force : replace photos that are already set}
                            {--dry : only show the choice, download nothing}';

    protected $description = 'Download free car photos from Wikipedia and Wikimedia Commons and fill image_path and image_credit';

    private const WIKI_API = 'https://en.wikipedia.org/w/api.php';
    private const COMMONS_API = 'https://commons.wikimedia.org/w/api.php';

    // Wikimedia просит в User-Agent название проекта и контакт
    private const USER_AGENT = 'AutoDuelStudentProject/1.0 (https://github.com/arkasaa73-spec; educational project)';

    // Для этих машин название на Commons отличается от нашего, поэтому ищем по другому тексту
    private const SEARCH_OVERRIDES = [
        'Mazda Mazda3' => 'Mazda3',
        'Mazda MX-5 Miata' => 'Mazda MX-5',
        'Toyota RAV4 Hybrid' => 'Toyota RAV4',
        'Toyota GR Supra 3.0' => 'Toyota GR Supra',
        'Nissan Z' => 'Nissan Z RZ34',
        'BMW 330i' => 'BMW G20',
        'BMW M3' => 'BMW M3 G80',
        'BMW M5' => 'BMW M5 F90',
        'Audi RS6 Avant' => 'Audi RS6',
        'Audi R8 V10' => 'Audi R8',
        'Mercedes-AMG GT 63 S' => 'Mercedes-AMG GT 63',
        'Mercedes-Benz G 63 AMG' => 'Mercedes-AMG G 63',
        'Lamborghini Huracan EVO' => 'Lamborghini Huracán EVO',
        'Lamborghini Urus S' => 'Lamborghini Urus',
        'Porsche Taycan Turbo S' => 'Porsche Taycan',
        'Porsche 911 Carrera' => 'Porsche 911 992',
        'Honda NSX Type S' => 'Honda NSX',
        'Tesla Model S Plaid' => 'Tesla Model S',
        'Dodge Challenger SRT Hellcat' => 'Dodge Challenger Hellcat',
        'Chevrolet Corvette Stingray' => 'Chevrolet Corvette C8',
        'Chevrolet Camaro SS' => 'Chevrolet Camaro',
        'Land Rover Defender 110' => 'Land Rover Defender',
        'Lexus RX 350' => 'Lexus RX',
    ];

    // У этих машин кабриолет, пикап или мягкая крыша - нормальный вариант кузова, их не отбрасываем
    private const BODY_EXEMPT = ['mx-5', 'f-150', 'wrangler'];

    // Слова в названии файла, из-за которых снимок отбрасывается (салон, ракурс сзади или сбоку, детали, не-фото)
    private const BAD_WORDS = '/\b(interior|engine|dashboard|cockpit|seat|seats|steering|wheel|wheels|rim|logo|badge|emblem|headlight|taillight|detail|trunk|cutaway|drawing|sketch|render|diagram|brochure|poster|toy|lego|diecast|scale|crash|wreck|wrecked|burnt|accident|police|taxi|ambulance|race|racing|rally|drift|tuned|tuning|concept|prototype|rear|back|side|profile)\b/';

    // Категории Commons, по которым видно неудачный ракурс или деталь
    private const BAD_VIEWS = '/\b(rear|back|side|interior|interiors|emblem|emblems|badge|badges|logo|logos|detail|details)\b/';

    // Нестандартный кузов и тюнинг (ломают соответствие нашей машине)
    private const BAD_BODY = '/\b(cabrio|cabriolet|convertible|convertibles|roadster|spyder|spider|targa|pickup|pick up|custom|modified|widebody|wrapped|stance|lowered|restomod|replica|brabus|mansory|novitec|techart|hamann|vorsteiner|liberty walk)\b/';

    // Снимки с выставок и музеев часто с людьми и мусором в кадре
    private const EVENT_WORDS = '/\b(show|expo|exhibition|salon|museum|fair|parade|festival|treffen|meeting)\b/';

    public function handle(): int
    {
        $query = Car::query()->orderBy('id');
        if ($this->option('car')) {
            $query->whereKey((int) $this->option('car'));
        }
        $cars = $query->get();

        if ($cars->isEmpty()) {
            $this->error('No cars found. Run: php artisan db:seed');

            return self::FAILURE;
        }

        $single = (bool) $this->option('car');
        if (! $single && ($this->option('query') || $this->option('file'))) {
            $this->error('--query and --file need --car=ID');

            return self::FAILURE;
        }

        $pick = max(1, (int) $this->option('pick'));
        $done = 0;
        $skipped = 0;
        $failed = [];

        foreach ($cars as $index => $car) {
            $label = $car->brand . ' ' . $car->model . ' ' . $car->year;

            if ($car->image_path && ! $this->option('force')) {
                $skipped++;
                continue;
            }

            if ($this->option('file')) {
                $candidates = $this->candidatesFromFile((string) $this->option('file'));
            } else {
                $term = $this->option('query') ?: $this->termFor($car);
                $candidates = $this->findCandidates((string) $term, $car);

                // Запасной поиск: марка и первое слово модели
                if (! $candidates && ! $this->option('query')) {
                    $fallback = $car->brand . ' ' . Str::before($car->model, ' ');
                    if ($fallback !== $term) {
                        $candidates = $this->findCandidates($fallback, $car);
                    }
                }
            }

            if (! $candidates) {
                $this->warn(sprintf('[%d/%d] id %d  %s: nothing suitable found', $index + 1, $cars->count(), $car->id, $label));
                $failed[] = $car;
                continue;
            }

            $chosen = $candidates[min($pick, count($candidates)) - 1];
            $this->line(sprintf('[%d/%d] id %d  %s -> %s', $index + 1, $cars->count(), $car->id, $label, $chosen['title']));

            if ($this->option('dry')) {
                $this->line('        ' . $chosen['page_url']);
                continue;
            }

            $path = $this->download($chosen['download_url'], $car);
            if (! $path) {
                $this->warn('        download failed');
                $failed[] = $car;
                continue;
            }

            $car->update(['image_path' => $path, 'image_credit' => $chosen['credit']]);
            $done++;

            usleep(400_000); // не засыпаем Wikimedia запросами
        }

        $this->newLine();
        $this->info("Saved: {$done}, already had photo: {$skipped}, failed: " . count($failed));

        foreach ($failed as $car) {
            $this->line(sprintf('  id %d  %s %s   retry: php artisan cars:photos --car=%d --force --query="..."', $car->id, $car->brand, $car->model, $car->id));
        }

        return self::SUCCESS;
    }

    private function termFor(Car $car): string
    {
        $name = $car->brand . ' ' . $car->model;

        return self::SEARCH_OVERRIDES[$name] ?? $name;
    }

    // Собираем кандидатов из двух источников и сортируем: лучшие первыми
    private function findCandidates(string $term, Car $car): array
    {
        $tokens = $this->tokens($term);
        $pages = [];

        // Источник 1: главные фото статей Википедии (там обычно удачный ракурс спереди)
        $leadFiles = $this->wikipediaLeadFiles($term);
        if ($leadFiles) {
            foreach ($this->commonsPages(['titles' => implode('|', $leadFiles)]) as $page) {
                $page['lead'] = true;
                $pages[$page['title']] = $page;
            }
        }
        usleep(300_000);

        // Источник 2: поиск по файлам Commons, сначала с годом, потом без
        foreach ([$term . ' ' . $car->year, $term] as $search) {
            $found = $this->commonsPages([
                'generator' => 'search',
                'gsrsearch' => $search,
                'gsrnamespace' => 6, // пространство имён File:
                'gsrlimit' => 40,
            ]);
            foreach ($found as $page) {
                $pages[$page['title']] ??= $page;
            }
            usleep(300_000);
        }

        return $this->rank(array_values($pages), $tokens, $car);
    }

    // Фото, заданное вручную через --file
    private function candidatesFromFile(string $file): array
    {
        $name = preg_replace('/^File:/i', '', trim($file));
        $candidates = [];

        foreach ($this->commonsPages(['titles' => 'File:' . $name]) as $page) {
            $candidate = $this->toCandidate($page);
            if ($candidate) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    // Главные фото статей Википедии, названия которых подходят под запрос
    private function wikipediaLeadFiles(string $term): array
    {
        $needed = array_slice($this->tokens($term), 0, 2); // марка и первое слово модели
        $data = $this->request(self::WIKI_API, [
            'action' => 'query',
            'format' => 'json',
            'formatversion' => 2,
            'generator' => 'search',
            'gsrsearch' => $term,
            'gsrnamespace' => 0,
            'gsrlimit' => 6,
            'prop' => 'pageimages',
            'piprop' => 'name',
            'pilimit' => 6,
        ]);

        $files = [];
        foreach ($data['query']['pages'] ?? [] as $page) {
            if (empty($page['pageimage'])) {
                continue;
            }

            $title = $this->normalize($page['title'] ?? '');
            foreach ($needed as $token) {
                if (! preg_match('/\b' . preg_quote($token, '/') . '\b/', $title)) {
                    continue 2;
                }
            }

            $files[] = 'File:' . str_replace('_', ' ', $page['pageimage']);
        }

        return array_values(array_unique($files));
    }

    // Запрос к Commons: сведения о файлах (размер, ссылка на уменьшенную копию, автор, лицензия, категории)
    private function commonsPages(array $extra): array
    {
        $params = array_merge([
            'action' => 'query',
            'format' => 'json',
            'formatversion' => 2,
            'prop' => 'imageinfo|categories',
            'iiprop' => 'url|size|mime|extmetadata',
            'iiurlwidth' => 1280, // сразу просим уменьшенную копию шириной 1280
            'iiextmetadatafilter' => 'Artist|LicenseShortName|NonFree',
            'cllimit' => 'max',
        ], $extra);

        $data = $this->request(self::COMMONS_API, $params);

        return $data['query']['pages'] ?? [];
    }

    private function request(string $url, array $params): array
    {
        try {
            $response = Http::withUserAgent(self::USER_AGENT)
                ->timeout(30)
                ->retry(3, 2000, throw: false)
                ->get($url, $params);
        } catch (Throwable $e) {
            $this->warn('        request failed: ' . $e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            $this->warn('        server answered HTTP ' . $response->status());

            return [];
        }

        return $response->json() ?? [];
    }

    // Оставляем подходящие фото и расставляем очки
    private function rank(array $pages, array $tokens, Car $car): array
    {
        $year = (int) $car->year;
        $carName = Str::lower($car->brand . ' ' . $car->model);
        $bodyExempt = false;
        foreach (self::BODY_EXEMPT as $word) {
            if (str_contains($carName, $word)) {
                $bodyExempt = true;
            }
        }

        $result = [];

        foreach ($pages as $page) {
            $info = $page['imageinfo'][0] ?? null;
            if (! $info || ($info['mime'] ?? '') !== 'image/jpeg') {
                continue;
            }

            // Нужны крупные кадры без панорам и вертикальных снимков
            $width = (int) ($info['width'] ?? 0);
            $height = (int) ($info['height'] ?? 0);
            if ($width < 1600 || $height < 1) {
                continue;
            }
            $ratio = $width / $height;
            if ($ratio < 1.3 || $ratio > 1.9) {
                continue;
            }

            $meta = $info['extmetadata'] ?? [];
            if (strtolower((string) ($meta['NonFree']['value'] ?? '')) === 'true') {
                continue;
            }

            $title = $this->normalize($page['title'] ?? '');
            $categories = $this->normalize(implode(' ', array_column($page['categories'] ?? [], 'title')));
            // Привод и расположение двигателя не говорят о ракурсе, убираем эти фразы
            $categories = preg_replace('/\b(front|rear|all|four) wheel drive\b|\b(front|mid|rear) engined?\b/', ' ', $categories);
            $both = $title . ' ' . $categories;

            if (preg_match(self::BAD_WORDS, $title) || preg_match(self::BAD_VIEWS, $categories)) {
                continue;
            }
            if (! $bodyExempt && preg_match(self::BAD_BODY, $both)) {
                continue;
            }

            // Марка и модель должны встречаться в названии файла или его категориях
            foreach ($tokens as $token) {
                if (! preg_match('/\b' . preg_quote($token, '/') . '\b/', $both)) {
                    continue 2;
                }
            }

            // Год в названии файла: снимок явно старой машины отбрасываем
            if (preg_match_all('/\b(19\d\d|20\d\d)\b/', $title, $years)) {
                if (max(array_map('intval', $years[1])) < $year - 4) {
                    continue;
                }
            }

            $candidate = $this->toCandidate($page);
            if (! $candidate) {
                continue;
            }

            $score = 0;
            if (! empty($page['lead'])) {
                $score += 4; // главное фото статьи Википедии
            }
            if (preg_match('/\bfront\b/', $both)) {
                $score += 3; // ракурс спереди
            }
            if (preg_match('/\b' . $year . '\b/', $title)) {
                $score += 5;
            } elseif (preg_match('/\b(' . ($year - 1) . '|' . ($year + 1) . ')\b/', $title)) {
                $score += 2;
            }
            if ($width >= 2400) {
                $score += 1;
            }
            if (preg_match(self::EVENT_WORDS, $title)) {
                $score -= 3;
            }

            $candidate['score'] = $score;
            $result[] = $candidate;
        }

        // Сначала больше очков, при равенстве выше в выдаче поиска
        usort($result, fn ($a, $b) => [$b['score'], $a['index']] <=> [$a['score'], $b['index']]);

        return $result;
    }

    // Данные для скачивания и подписи из ответа Commons (без проверок)
    private function toCandidate(array $page): ?array
    {
        $info = $page['imageinfo'][0] ?? null;
        $url = $info['thumburl'] ?? $info['url'] ?? null;
        if (! $info || ! $url) {
            return null;
        }

        return [
            'title' => $page['title'],
            'index' => (int) ($page['index'] ?? 999), // порядок в выдаче поиска
            'score' => 0,
            'download_url' => $url,
            'page_url' => $info['descriptionurl'] ?? '',
            'credit' => $this->credit($info['extmetadata'] ?? []),
        ];
    }

    // Подпись для карточки: автор, лицензия, источник
    private function credit(array $meta): string
    {
        $artist = html_entity_decode(strip_tags((string) ($meta['Artist']['value'] ?? '')));
        $artist = trim(preg_replace('/\s+/', ' ', $artist));
        $artist = Str::limit($artist, 80, '');
        $license = $meta['LicenseShortName']['value'] ?? 'Wikimedia Commons';

        return ($artist !== '' ? $artist : 'Автор не указан') . ' · ' . $license . ' · Wikimedia Commons';
    }

    private function download(string $url, Car $car): ?string
    {
        try {
            $response = Http::withUserAgent(self::USER_AGENT)
                ->timeout(60)
                ->retry(3, 2000, throw: false)
                ->get($url);
        } catch (Throwable) {
            return null;
        }

        $body = $response->body();

        // Проверяем, что получили именно JPEG (он начинается с байтов FF D8)
        if (! $response->successful() || strlen($body) < 10000 || substr($body, 0, 2) !== "\xFF\xD8") {
            return null;
        }

        $relative = 'images/cars/' . Str::slug($car->brand . ' ' . $car->model . ' ' . $car->year) . '.jpg';
        File::ensureDirectoryExists(public_path('images/cars'));
        File::put(public_path($relative), $body);

        return $relative;
    }

    // Нижний регистр, без диакритики и знаков: "Huracán EVO" -> "huracan evo"
    private function normalize(string $text): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($text))));
    }

    private function tokens(string $term): array
    {
        return array_values(array_filter(explode(' ', $this->normalize($term))));
    }
}
