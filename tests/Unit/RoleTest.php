<?php

namespace Tests\Unit;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_only_admin_and_engineer_can_manage_cases(): void
    {
        $this->assertTrue(Role::Admin->canManageCases());
        $this->assertTrue(Role::Engineer->canManageCases());
        $this->assertFalse(Role::Viewer->canManageCases());
    }
}
