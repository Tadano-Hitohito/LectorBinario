<?php

namespace App\Models;

use CodeIgniter\Model;

class loginModel extends Model
{
    private $users = [
        [
            'id' => 1,
            'username' => 'alex',
            'password' => 'password',
            'nombre' => 'Alex',
            'apellido' => 'Rodrigues'
        ],
        [
            'id' => 2,
            'username' => 'andres',
            'password' => 'metalgear',
            'nombre' => 'Andres',
            'apellido' => 'Carpio'
        ],
        [
            'id' => 3,
            'username' => 'scarlet',
            'password' => 'shantae64',
            'nombre' => 'Scarlet',
            'apellido' => 'Torres'
        ],
        [
            'id' => 4,
            'username' => 'carlos',
            'password' => 'deadoralive',
            'nombre' => 'Carlos',
            'apellido' => 'Barrios'
        ],
        [
            'id' => 5,
            'username' => 'sofia',
            'password' => 'blazblue',
            'nombre' => 'Sofia',
            'apellido' => 'Herrera'
        ]
    ];

    public function getUserByUsername(string $username)
    {
        foreach ($this->users as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }

        return null;
    }
}

