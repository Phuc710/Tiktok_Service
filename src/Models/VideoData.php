<?php

namespace App\Models;

class VideoData {
    public string $id          = '';
    public string $title       = '';
    public string $cover       = '';
    public string $authorName  = '';
    public string $authorNickname = '';
    public string $authorAvatar = '';
    public string $playUrl     = '';
    public string $musicUrl    = '';
    public string $musicTitle  = '';
    public string $musicAuthor = '';
    public array  $images      = [];

    // Stats
    public int $viewCount    = 0;
    public int $likeCount    = 0;
    public int $commentCount = 0;
    public int $shareCount   = 0;

    public function __construct(array $data = []) {
        if (empty($data)) return;

        $this->id            = $data['id']    ?? '';
        $this->title         = $data['title'] ?? '';
        $this->cover         = $data['cover'] ?? '';

        $this->authorName     = $data['author']['unique_id'] ?? 'unknown';
        $this->authorNickname = $data['author']['nickname']  ?? '';
        $this->authorAvatar   = $data['author']['avatar']    ?? '';

        // HD link trước, fallback sang play link
        $this->playUrl  = $data['hdplay'] ?? ($data['play'] ?? '');

        // Music info
        $music = $data['music_info'] ?? [];
        $this->musicUrl    = $data['music'] ?? ($music['play'] ?? ($music['play_url'] ?? ''));
        $this->musicTitle  = $music['title']    ?? 'Unknown Title';
        $this->musicAuthor = $music['author']   ?? 'Unknown Author';

        // Slideshow images
        $this->images = $data['images'] ?? [];

        // Stats
        $this->viewCount    = (int)($data['play_count']    ?? 0);
        $this->likeCount    = (int)($data['digg_count']    ?? 0);
        $this->commentCount = (int)($data['comment_count'] ?? 0);
        $this->shareCount   = (int)($data['share_count']   ?? 0);
    }

    public function isSlideshow(): bool {
        return !empty($this->images) && is_array($this->images);
    }

    /**
     * Xuất dữ liệu đầy đủ dạng array (dùng cho API JSON response)
     */
    public function toArray(): array {
        return [
            'id'     => $this->id,
            'title'  => $this->title,
            'cover'  => $this->cover,
            'type'   => $this->isSlideshow() ? 'photo' : 'video',
            'author' => [
                'username' => $this->authorName,
                'nickname' => $this->authorNickname,
                'avatar'   => $this->authorAvatar,
            ],
            'video'  => [
                'download_url' => $this->playUrl,
            ],
            'music'  => [
                'title'        => $this->musicTitle,
                'author'       => $this->musicAuthor,
                'download_url' => $this->musicUrl,
            ],
            'images' => $this->images,
            'stats'  => [
                'views'    => $this->viewCount,
                'likes'    => $this->likeCount,
                'comments' => $this->commentCount,
                'shares'   => $this->shareCount,
            ],
        ];
    }
}
