<?php
namespace Tests\Unit\Application\Service;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\Application\Service\AuthService;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Application\Exception\ValidationException;
use App\Application\Exception\AuthenticationException;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private AuthService $authService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    /**
     * @throws ValidationException
     */
    public function testSuccessfulRegistration(): void
    {
        $userData = [
            'nickname' => 'johndoe',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'phone' => '+1234567890'
        ];

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($userData['email'])
            ->willReturn(null);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function(User $user) {
                $user->setId(1);
            });

        $user = $this->authService->register($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['email'], $user->getEmail());
        $this->assertEquals($userData['nickname'], $user->getNickname());
    }

    public function testRegistrationWithExistingEmail(): void
    {
        $userData = [
            'nickname' => 'johndoe',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'password123'
        ];

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(new User('existing', 'Existing', 'User', null, 'existing@example.com', 'password'));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email already exists');

        $this->authService->register($userData);
    }

    public function testSuccessfulLogin(): void
    {
        $email = 'john@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $user = new User('johndoe', 'John', 'Doe', null, $email, $hashedPassword);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $loggedInUser = $this->authService->login($email, $password);

        $this->assertInstanceOf(User::class, $loggedInUser);
        $this->assertEquals($email, $loggedInUser->getEmail());
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $email = 'john@example.com';
        $password = 'wrongpassword';

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login($email, $password);
    }
}