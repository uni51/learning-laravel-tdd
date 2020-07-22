<?php

namespace Tests\Unit\Models;

use App\Models\Lesson;
use App\Models\User;
use App\Models\UserProfile;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    /**
     * @param string $plan
     * @param int $remainingCount
     * @param int $reservationCount
     * @throws \Exception
     * @dataProvider dataCanReserve_正常
     */
    public function testCanReserve_正常(string $plan, int $remainingCount, int $reservationCount)
    {
        /** @var User $user */
        $user = Mockery::mock(User::class)->makePartial(); // makePartialで、パーシャルモックになる
        $user->shouldReceive('reservationCountThisMonth')->andReturn($reservationCount);
        $user->profile = new UserProfile();
        $user->profile->plan = $plan;

        // Mockery でモックを作ると、変数の型が Mock になってしまうので、DocComment でドメインの型を指定しておく
        /** @var Lesson $lesson */
        $lesson = Mockery::mock(Lesson::class);
        $lesson->shouldReceive('remainingCount')->andReturn($remainingCount);

        $user->canReserve($lesson);
        // 例外が出ないことを確認するアサーションがないので代わりに
        $this->assertTrue(true);
    }

    /**
     * @param string $plan
     * @param int $remainingCount
     * @param int $reservationCount
     * @param string $errorMessage
     * @throws \Exception
     * @dataProvider dataCanReserve_異常
     */
    public function testCanReserve_エラー(string $plan, int $remainingCount, int $reservationCount, string $errorMessage)
    {
        /** @var User $user */
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('reservationCountThisMonth')->andReturn($reservationCount);
        $user->profile = new UserProfile();
        $user->profile->plan = $plan;

        /** @var Lesson $lesson */
        $lesson = Mockery::mock(Lesson::class);
        $lesson->shouldReceive('remainingCount')->andReturn($remainingCount);

        // エラーメッセージを添えて、例外を返す
        $this->expectExceptionMessage($errorMessage);

        $user->canReserve($lesson);
    }

    public function dataCanReserve_正常()
    {
        return [
            '予約可:レギュラー,空きあり,月の上限以下' => [
                'plan' => 'regular',
                'remainingCount' => 1,
                'reservationCount' => 4,
                'canReserve' => true,
            ],
            '予約可:ゴールド,空きあり' => [
                'plan' => 'gold',
                'remainingCount' => 1,
                'reservationCount' => 5,
                'canReserve' => true,
            ],
        ];
    }

    public function dataCanReserve_異常()
    {
        return [
            '予約不可:レギュラー,空きあり,月の上限' => [
                'plan' => 'regular',
                'remainingCount' => 1,
                'reservationCount' => 5,
                'errorMessage' => '今月の予約がプランの上限に達しています。',
            ],
            '予約不可:レギュラー,空きなし,月の上限以下' => [
                'plan' => 'regular',
                'remainingCount' => 0,
                'reservationCount' => 4,
                'errorMessage' => 'レッスンの予約可能上限に達しています。',
            ],
            '予約不可:ゴールド,空きなし' => [
                'plan' => 'gold',
                'remainingCount' => 0,
                'reservationCount' => 5,
                'errorMessage' => 'レッスンの予約可能上限に達しています。',
            ],
        ];
    }
}
