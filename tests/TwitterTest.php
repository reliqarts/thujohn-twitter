<?php

declare(strict_types=1);

namespace ReliqArts\Thujohn\Twitter\Tests;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReliqArts\Thujohn\Twitter\Twitter;

/**
 * @internal
 * @coversNothing
 */
final class TwitterTest extends TestCase
{
    /**
     * @param string $endpoint
     * @param string $testedMethod
     * @param array  $params
     */
    public function paramTest(string $endpoint, string $testedMethod, array $params)
    {
        $twitter = $this->getTwitterExpecting($endpoint, $params);

        $twitter->{$testedMethod}($params);
    }

    public function testGetUsersWithScreenName(): void
    {
        $twitter = $this->getTwitterExpecting('users/show', [
            'screen_name' => 'my_screen_name',
        ]);

        $twitter->getUsers([
            'screen_name' => 'my_screen_name',
        ]);
    }

    public function testGetUsersWithId(): void
    {
        $twitter = $this->getTwitterExpecting('users/show', [
            'user_id' => 1234567890,
        ]);

        $twitter->getUsers([
            'user_id' => 1234567890,
        ]);
    }

    public function testGetUsersInvalid(): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getUsers([
            'include_entities' => true,
        ]);
    }

    public function testGetUsersLookupWithIds(): void
    {
        $twitter = $this->getTwitterExpecting('users/lookup', [
            'user_id' => '1,2,3,4',
        ]);

        $twitter->getUsersLookup([
            'user_id' => implode(',', [1, 2, 3, 4]),
        ]);
    }

    public function testGetUsersLookupWithScreenNames(): void
    {
        $twitter = $this->getTwitterExpecting('users/lookup', [
            'screen_name' => 'me,you,everybody',
        ]);

        $twitter->getUsersLookup([
            'screen_name' => implode(',', ['me', 'you', 'everybody']),
        ]);
    }

    public function testGetUsersLookupInvalid(): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getUsersLookup([
            'include_entities' => true,
        ]);
    }

    /**
     * getList can accept list_id, or slug and owner_screen_name, or slug and owner_id.
     *
     * Use a Data Provider to test this method with different params without repeating our code
     *
     * @dataProvider providerGetList
     *
     * @param array $params
     */
    public function testGetList(array $params): void
    {
        $this->paramTest('lists/show', 'getList', $params);
    }

    public function providerGetList(): array
    {
        return [
            [
                ['list_id' => 1],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1],
            ],
        ];
    }

    /**
     * @dataProvider providerGetListBad
     *
     * @param array $params
     */
    public function testGetListFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();
        $twitter->getList($params);
    }

    public function providerGetListBad(): array
    {
        return [
            [
                ['slug' => 1],
            ],
        ];
    }

    /**
     * getListMembers can accept list_id, or slug and owner_screen_name, or slug and owner_id.
     *
     * @dataProvider providerGetListMembers
     *
     * @param array $params
     */
    public function testGetListMembers(array $params): void
    {
        $this->paramTest('lists/members', 'getListMembers', $params);
    }

    public function providerGetListMembers(): array
    {
        return [
            [
                ['list_id' => 1],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1],
            ],
        ];
    }

    /**
     * @dataProvider providerGetListMembersBad
     *
     * @param array $params
     */
    public function testGetListMembersFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();

        $twitter->getListMembers($params);
    }

    public function providerGetListMembersBad(): array
    {
        return [
            [
                ['slug' => 'sweetheart_to_miss'],
            ],
        ];
    }

    /**
     * getListMember can accept list_id and user_id, or list_id and screen_name,
     * or slug and owner_screen_name and user_id, or slug and owner_screen_name and screen_name,
     * or slug and owner_id and user_id, or slug and owner_id and screen_name.
     *
     * @dataProvider providerGetListMember
     *
     * @param array $params
     */
    public function testGetListMember(array $params): void
    {
        $this->paramTest('lists/members/show', 'getListMember', $params);
    }

    public function providerGetListMember(): array
    {
        return [
            [
                ['list_id' => 1, 'user_id' => 2],
            ],
            [
                ['list_id' => 1, 'screen_name' => 'jake'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood', 'user_id' => 2],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_screen_name' => 'elwood', 'screen_name' => 'jake'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1, 'screen_name' => 'jake'],
            ],
            [
                ['slug' => 'sugar_to_kiss', 'owner_id' => 1, 'user_id' => 2],
            ],
        ];
    }

    /**
     * @dataProvider providerGetListMemberBad
     *
     * @param array $params
     */
    public function testGetListMemberFails(array $params): void
    {
        $this->expectException(Exception::class);

        $twitter = $this->getTwitter();
        $twitter->getListMembers($params);
    }

    public function providerGetListMemberBad(): array
    {
        return [
            [
                ['slug' => 'sweetheart_to_miss'],
            ],
        ];
    }

    /**
     * @return MockObject|Twitter
     */
    protected function getTwitter(): MockObject
    {
        return $this->getMockBuilder(Twitter::class)
            ->onlyMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $endpoint
     * @param array  $queryParams
     *
     * @return MockObject|Twitter
     */
    protected function getTwitterExpecting(string $endpoint, array $queryParams): MockObject
    {
        $twitter = $this->getTwitter();
        $twitter->expects($this->once())
            ->method('query')
            ->with(
                $endpoint,
                $this->anything(),
                $queryParams
            );

        return $twitter;
    }
}
