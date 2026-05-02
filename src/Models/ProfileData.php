<?php

namespace App\Models;

/**
 * ProfileData — Thông tin profile TikTok
 */
class ProfileData {

    public readonly string $uid;
    public readonly string $secUid;
    public readonly string $username;
    public readonly string $nickname;
    public readonly string $avatar;
    public readonly int    $followerCount;
    public readonly int    $followingCount;
    public readonly int    $videoCount;
    public readonly int    $likeCount;
    public readonly string $bio;

    public function __construct(array $data) {
        $this->uid            = (string)($data['uid']             ?? $data['id']            ?? '');
        $this->secUid         = (string)($data['secUid']          ?? $data['sec_uid']       ?? '');
        $this->username       = (string)($data['unique_id']       ?? $data['uniqueId']      ?? '');
        $this->nickname       = (string)($data['nickname']        ?? $this->username);
        $this->avatar         = (string)($data['avatar']          ?? $data['avatarThumb']   ?? $data['avatar_thumb'] ?? '');
        $this->followerCount  = (int)   ($data['follower_count']  ?? $data['followerCount'] ?? 0);
        $this->followingCount = (int)   ($data['following_count'] ?? $data['followingCount'] ?? 0);
        $this->videoCount     = (int)   ($data['aweme_count']     ?? $data['videoCount']    ?? $data['video_count'] ?? 0);
        $this->likeCount      = (int)   ($data['total_favorited'] ?? $data['heart']         ?? $data['diggCount']   ?? 0);
        $this->bio            = (string)($data['signature']       ?? $data['bio']           ?? '');
    }

    public function toArray(): array {
        return [
            'uid'             => $this->uid,
            'sec_uid'         => $this->secUid,
            'username'        => $this->username,
            'nickname'        => $this->nickname,
            'avatar'          => $this->avatar,
            'follower_count'  => $this->followerCount,
            'following_count' => $this->followingCount,
            'video_count'     => $this->videoCount,
            'like_count'      => $this->likeCount,
            'bio'             => $this->bio,
        ];
    }
}
