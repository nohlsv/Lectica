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
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string $content
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $file_hash
 * @property string|null $description
 * @property int $verified
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Flashcard> $flashcards
 * @property-read int $flashcards_count
 * @property-read bool $can_edit
 * @property-read bool $is_starred
 * @property-read int|null $quizzes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quiz> $quizzes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $starredBy
 * @property-read int|null $starred_by_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\FileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File verified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFileHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereVerified($value)
 */
	class File extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $file_id
 * @property string $question
 * @property string $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\File $file
 * @method static \Database\Factories\FlashcardFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereUpdatedAt($value)
 */
	class Flashcard extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $player_one_id
 * @property int|null $player_two_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $player_one_score
 * @property int $player_two_score
 * @property int|null $current_turn
 * @property array<array-key, mixed>|null $questions
 * @property string $status
 * @property string|null $game_end_reason
 * @property-read mixed $phase
 * @property-read \App\Models\User $playerOne
 * @property-read \App\Models\User|null $playerTwo
 * @method static \Database\Factories\GameFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame whereCurrentTurn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame whereGameEndReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame wherePlayerOneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame wherePlayerOneScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame wherePlayerTwoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame wherePlayerTwoScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame whereQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MultiplayerGame whereUpdatedAt($value)
 */
	class Game extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $file_id
 * @property string $type
 * @property int $correct_answers
 * @property int $total_questions
 * @property string|null $mistakes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\File $file
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereCorrectAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereMistakes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereTotalQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeRecord whereUserId($value)
 */
	class PracticeRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $college
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\ProgramFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereUpdatedAt($value)
 */
	class Program extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $file_id
 * @property string $question
 * @property \App\Enums\QuizType $type
 * @property array<array-key, mixed>|null $options
 * @property array<array-key, mixed> $answers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\File $file
 * @method static \Database\Factories\QuizFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quiz whereUpdatedAt($value)
 */
	class Quiz extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @method static \Database\Factories\TagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereUpdatedAt($value)
 */
	class Tag extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $year_of_study
 * @property int|null $program_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $user_role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Program|null $program
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $starredFiles
 * @property-read int|null $starred_files_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereYearOfStudy($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

