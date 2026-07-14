<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function index()
    {
        $featuredPost = BlogPost::with('author')
            ->published()
            ->latest('published_at')
            ->first();

        $posts = BlogPost::with('author')
            ->published()
            ->when($featuredPost, fn ($query) => $query->where('id', '!=', $featuredPost->id))
            ->latest('published_at')
            ->paginate(9);

        return view('blog.index', compact('featuredPost', 'posts'));
    }

    public function show(BlogPost $blogPost)
    {
        abort_unless($blogPost->is_published && $blogPost->published_at && $blogPost->published_at->lte(now()), 404);

        $latestPosts = BlogPost::with('author')
            ->published()
            ->where('id', '!=', $blogPost->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('blogPost', 'latestPosts'));
    }

    public function adminIndex()
    {
        $this->ensureAdmin();

        $posts = BlogPost::with('author')
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::where('is_published', true)->count(),
            'drafts' => BlogPost::where('is_published', false)->count(),
            'this_month' => BlogPost::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('dashboards.admin_blog', compact('posts', 'stats'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validatedData($request);
        BlogPost::create($this->buildPayload($data));

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully.');
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $this->ensureAdmin();

        $data = $this->validatedData($request, $blogPost);
        $blogPost->update($this->buildPayload($data, $blogPost));

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully.');
    }

    public function uploadMedia(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'upload' => [
                'required',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,gif,bmp,avif,mp4,mov,webm,m4v,mp3,wav,ogg,m4a,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip',
            ],
        ]);

        $file = $request->file('upload');
        $path = $this->storeUploadedFile($file, 'content');

        return response()->json([
            'url' => $path,
            'media_type' => $this->detectMediaCategory($file->getMimeType(), $file->getClientOriginalExtension()),
            'file_name' => $file->getClientOriginalName(),
        ]);
    }

    public function destroy(BlogPost $blogPost)
    {
        $this->ensureAdmin();

        $blogPost->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted successfully.');
    }

    protected function validatedData(Request $request, ?BlogPost $blogPost = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('blog_posts', 'slug')->ignore($blogPost?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'url', 'max:2048'],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
    }

    protected function buildPayload(array $data, ?BlogPost $blogPost = null): array
    {
        $isPublished = request()->boolean('is_published');
        $slug = $this->generateUniqueSlug($data['slug'] ?: $data['title'], $blogPost?->id);
        $coverImage = $this->resolveCoverImage($data, $blogPost);
        $content = $this->sanitizeHtml($data['content']);

        return [
            'user_id' => $blogPost?->user_id ?? Auth::id(),
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?: Str::limit(trim(strip_tags($content)), 180),
            'content' => $content,
            'cover_image' => $coverImage,
            'is_published' => $isPublished,
            'published_at' => $isPublished
                ? ($blogPost?->published_at ?? now())
                : null,
        ];
    }

    protected function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value) ?: 'blog-post';
        $slug = $baseSlug;
        $counter = 2;

        while (
            BlogPost::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function resolveCoverImage(array $data, ?BlogPost $blogPost = null): ?string
    {
        if (request()->hasFile('cover_image_file')) {
            $newPath = $this->storeUploadedImage(request()->file('cover_image_file'));

            if ($blogPost?->cover_image) {
                $this->deleteLocalCoverImage($blogPost->cover_image);
            }

            return $newPath;
        }

        $coverImage = $data['cover_image'] ?: null;

        if (
            $blogPost?->cover_image &&
            $coverImage &&
            $coverImage !== $blogPost->cover_image &&
            str_starts_with($blogPost->cover_image, '/uploads/blog/')
        ) {
            $this->deleteLocalCoverImage($blogPost->cover_image);
        }

        return $coverImage;
    }

    protected function storeUploadedImage($file): string
    {
        return $this->storeUploadedFile($file);
    }

    protected function storeUploadedFile($file, string $subdirectory = ''): string
    {
        $relativeDirectory = 'uploads/blog' . ($subdirectory ? '/' . trim($subdirectory, '/') : '');
        $directory = public_path($relativeDirectory);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = now()->format('YmdHis') . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return '/' . $relativeDirectory . '/' . $filename;
    }

    protected function deleteLocalCoverImage(string $path): void
    {
        if (!str_starts_with($path, '/uploads/blog/')) {
            return;
        }

        $fullPath = public_path(ltrim($path, '/'));

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    protected function detectMediaCategory(?string $mimeType, ?string $extension = null): string
    {
        if (is_string($mimeType) && str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (is_string($mimeType) && str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (is_string($mimeType) && str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        return in_array(strtolower((string) $extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif'], true)
            ? 'image'
            : 'file';
    }

    protected function sanitizeHtml(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        if (!class_exists(\DOMDocument::class)) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES, 'UTF-8'));
        }

        $allowedTags = [
            'a', 'audio', 'blockquote', 'br', 'code', 'div', 'em', 'figcaption', 'figure',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'iframe', 'img', 'li', 'ol', 'p',
            'pre', 's', 'source', 'span', 'strong', 'table', 'tbody', 'td', 'th', 'thead',
            'tr', 'u', 'ul', 'video',
        ];
        $allowedAttributes = [
            '*' => ['class'],
            'a' => ['href', 'target', 'rel'],
            'audio' => ['controls', 'preload', 'src'],
            'figure' => ['class'],
            'iframe' => ['allow', 'allowfullscreen', 'frameborder', 'height', 'loading', 'referrerpolicy', 'src', 'title', 'width'],
            'img' => ['alt', 'height', 'src', 'width'],
            'source' => ['src', 'type'],
            'td' => ['colspan', 'rowspan'],
            'th' => ['colspan', 'rowspan'],
            'video' => ['controls', 'height', 'muted', 'playsinline', 'poster', 'preload', 'src', 'width'],
        ];
        $booleanAttributes = ['allowfullscreen', 'controls', 'muted', 'playsinline'];

        $previousErrors = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $wrappedHtml = '<!DOCTYPE html><html><body>' . $html . '</body></html>';
        $dom->loadHTML(mb_convert_encoding($wrappedHtml, 'HTML-ENTITIES', 'UTF-8'));

        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);

            return '';
        }

        $this->sanitizeChildNodes($body, $allowedTags, $allowedAttributes, $booleanAttributes);

        $cleanHtml = $this->innerHtml($body);

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        return trim($cleanHtml);
    }

    protected function sanitizeChildNodes(\DOMNode $parent, array $allowedTags, array $allowedAttributes, array $booleanAttributes): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $parent->removeChild($child);
                continue;
            }

            if (!($child instanceof \DOMElement)) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (!in_array($tag, $allowedTags, true)) {
                $this->unwrapNode($child);
                continue;
            }

            $this->sanitizeElementAttributes($child, $tag, $allowedAttributes, $booleanAttributes);
            $this->sanitizeChildNodes($child, $allowedTags, $allowedAttributes, $booleanAttributes);
        }
    }

    protected function sanitizeElementAttributes(\DOMElement $element, string $tag, array $allowedAttributes, array $booleanAttributes): void
    {
        $allowedForTag = array_merge($allowedAttributes['*'] ?? [], $allowedAttributes[$tag] ?? []);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = trim($attribute->nodeValue);

            if (str_starts_with($name, 'on') || !in_array($name, $allowedForTag, true)) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            if (in_array($name, $booleanAttributes, true)) {
                $element->setAttribute($name, $name);
                continue;
            }

            if ($name === 'class') {
                $cleanClass = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $value) ?? '';
                $cleanClass = trim(preg_replace('/\s+/', ' ', $cleanClass) ?? '');

                if ($cleanClass === '') {
                    $element->removeAttribute('class');
                } else {
                    $element->setAttribute('class', $cleanClass);
                }

                continue;
            }

            if (in_array($name, ['href', 'src', 'poster'], true)) {
                $cleanUrl = $this->sanitizeUrl($value, $tag, $name);

                if ($cleanUrl === null) {
                    $element->removeAttribute($attribute->nodeName);
                } else {
                    $element->setAttribute($name, $cleanUrl);
                }

                continue;
            }

            if (in_array($name, ['width', 'height', 'colspan', 'rowspan'], true)) {
                if (!preg_match('/^\d{1,4}%?$/', $value)) {
                    $element->removeAttribute($attribute->nodeName);
                }

                continue;
            }

            if ($name === 'target') {
                $element->setAttribute('target', $value === '_blank' ? '_blank' : '_self');

                if ($value === '_blank' && !$element->hasAttribute('rel')) {
                    $element->setAttribute('rel', 'noopener noreferrer');
                }

                continue;
            }

            if ($name === 'rel') {
                $element->setAttribute('rel', trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', $value) ?? ''));
                continue;
            }

            if ($name === 'allow') {
                $element->setAttribute('allow', trim(preg_replace('/[^A-Za-z0-9;\s\-]/', '', $value) ?? ''));
                continue;
            }

            if ($name === 'loading') {
                $element->setAttribute('loading', $value === 'eager' ? 'eager' : 'lazy');
                continue;
            }

            if ($name === 'referrerpolicy') {
                $policy = in_array($value, ['no-referrer', 'strict-origin-when-cross-origin', 'origin'], true)
                    ? $value
                    : 'strict-origin-when-cross-origin';
                $element->setAttribute('referrerpolicy', $policy);
            }
        }
    }

    protected function sanitizeUrl(string $url, string $tag, string $attribute): ?string
    {
        if ($url === '') {
            return null;
        }

        if ($url[0] === '/' && !str_starts_with($url, '//')) {
            return $url;
        }

        if ($attribute === 'href' && in_array($url[0], ['#', '?'], true)) {
            return $url;
        }

        if ($attribute === 'href' && preg_match('/^(mailto|tel):/i', $url)) {
            return $url;
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            return null;
        }

        if ($tag === 'iframe' && !preg_match('/^https?:\/\//i', $url)) {
            return null;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    protected function unwrapNode(\DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (!$parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
        $this->sanitizeChildNodes($parent, [
            'a', 'audio', 'blockquote', 'br', 'code', 'div', 'em', 'figcaption', 'figure',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'iframe', 'img', 'li', 'ol', 'p',
            'pre', 's', 'source', 'span', 'strong', 'table', 'tbody', 'td', 'th', 'thead',
            'tr', 'u', 'ul', 'video',
        ], [
            '*' => ['class'],
            'a' => ['href', 'target', 'rel'],
            'audio' => ['controls', 'preload', 'src'],
            'figure' => ['class'],
            'iframe' => ['allow', 'allowfullscreen', 'frameborder', 'height', 'loading', 'referrerpolicy', 'src', 'title', 'width'],
            'img' => ['alt', 'height', 'src', 'width'],
            'source' => ['src', 'type'],
            'td' => ['colspan', 'rowspan'],
            'th' => ['colspan', 'rowspan'],
            'video' => ['controls', 'height', 'muted', 'playsinline', 'poster', 'preload', 'src', 'width'],
        ], ['allowfullscreen', 'controls', 'muted', 'playsinline']);
    }

    protected function innerHtml(\DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }
}
