<?php

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserTest extends TestCase {
  public function testToAnchor() {
    $User = new \App\Models\User([
      'name' => 'TestUser',
      'avatar_url' => '/img/guest.svg',
    ]);
    $result = $User->toAnchor();
    static::assertEquals("<a href='/@TestUser' class='da-userlink local'><span class='name'>TestUser</span></a>", $result, 'Testing default parameter return value format');
    $result = $User->toAnchor(\App\Models\User::WITH_AVATAR);
    static::assertEquals("<a href='/@TestUser' class='da-userlink local with-avatar provider-deviantart'><img src='/img/guest.svg' class='avatar' alt='avatar'><span class='name'>TestUser</span></a>", $result, 'Testing full format return value');
  }

  public function testToDALink() {
    $User = new \App\Models\User([
      'name' => 'TestUser',
      'avatar_url' => '/img/guest.svg',
    ]);
    $result = $User->toDALink();
    static::assertEquals('https://www.deviantart.com/testuser', $result, 'Testing URL format return value');
  }

  public function testToDAAnchor() {
    $User = new \App\Models\User([
      'name' => 'TestUser',
      'avatar_url' => '/img/guest.svg',
    ]);
    $result = $User->toDAAnchor();
    static::assertEquals("<a href='https://www.deviantart.com/testuser' class='da-userlink'><span class='name'>TestUser</span></a>", $result, 'Testing default parameter return value format');
    $result = $User->toDAAnchor(\App\Models\User::WITH_AVATAR);
    static::assertEquals("<a href='https://www.deviantart.com/testuser' class='da-userlink with-avatar'><img src='/img/guest.svg' class='avatar' alt='avatar'> <span class='name'>TestUser</span></a>", $result, 'Testing default parameter return value format');
  }

  public function testGetAvatarWrap() {
    $id = Uuid::uuid4();
    $User = new \App\Models\User([
      'id' => $id,
      'name' => 'TestUser',
      'avatar_url' => '/img/guest.svg',
    ]);
    $result = $User->getAvatarWrap(' app-illustrator');
    static::assertEquals("<div class='avatar-wrap provider-deviantart app-illustrator' data-for='$id'><img src='/img/guest.svg' class='avatar' alt='avatar'></div>", $result);
  }
}
