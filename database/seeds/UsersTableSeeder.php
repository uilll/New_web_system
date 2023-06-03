<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Tobuli\Repositories\User\UserRepositoryInterface as User;

class UsersTableSeeder extends Seeder
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        // TODO: Implement __construct() method.
        $this->user = $user;
    }

    public function run()
    {
        $users = [];

        $user = $this->user->create([
            //'email' => 'admin@gpswox.com',
            //'password' => 'raktelis9821',
            'group_id' => 1,
            'available_maps' => [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
            ],
            'open_device_groups' => '["0"]',
            'open_geofence_groups' => '["0"]',
        ]);

        $users[$user->id] = $user->id;

        $user = $this->user->create([
            'email' => 'admin@yourdomain.com',
            'password' => 'pass6342word',
            'group_id' => 1,
            'available_maps' => [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
            ],
            'open_device_groups' => '["0"]',
            'open_geofence_groups' => '["0"]',
        ]);

        $users[$user->id] = $user->id;

        foreach ($users as $key => $user) {
            $permissions = [];

            foreach (config('tobuli.permissions') as $name => $modes) {
                $permissions[] = array_merge([
                    'user_id' => $user,
                    'name' => $name,
                ], $modes);
            }

            DB::table('user_permissions')->insert($permissions);
        }
    }
}
