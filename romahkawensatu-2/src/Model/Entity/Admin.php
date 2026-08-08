<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

class Admin extends Entity
{
    protected array $_accessible = [
        'username' => true,
        'email' => true,
        'password' => true,       // virtual setter -> password_hash
        'full_name' => true,
        'phone_number' => true,
    ];

    // Never expose the hash in JSON/arrays.
    protected array $_hidden = ['password_hash'];

    /**
     * Setting $admin->password = '...' transparently bcrypt-hashes into
     * password_hash. Use this when creating admins or changing passwords.
     */
    protected function _setPassword(?string $password): ?string
    {
        if ($password === null || $password === '') {
            return null;
        }
        $this->password_hash = (new DefaultPasswordHasher())->hash($password);
        return $password;
    }
}
