<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $file_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $filename
 * @property string $mimetype
 * @property string $token
 * @property string $title
 * @property string $contentModificationDate
 * @property int $isFolder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereContentModificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereIsFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereMimetype($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Download whereUserAgent($value)
 */
	class Download extends \Eloquent {}
}

namespace App\Models{
/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $filename
 * @property string|null $mimetype
 * @property string $token
 * @property string $title
 * @property string|null $contentModificationDate
 * @property int $isFolder
 * @property int $isSensible
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Document> $includedFiles
 * @property-read int|null $included_files_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereContentModificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIsFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIsSensible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereMimetype($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 */
	class DownloadableFile extends \Eloquent {}
}

namespace App\Models{
/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

