<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidCpf;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ValidCpfTest extends TestCase
{
    #[DataProvider('validCpfs')]
    public function test_it_accepts_valid_cpfs(string $cpf): void
    {
        $validator = Validator::make(['cpf' => $cpf], ['cpf' => [new ValidCpf]]);

        $this->assertFalse($validator->fails());
    }

    #[DataProvider('invalidCpfs')]
    public function test_it_rejects_invalid_cpfs(string $cpf): void
    {
        $validator = Validator::make(['cpf' => $cpf], ['cpf' => [new ValidCpf]]);

        $this->assertTrue($validator->fails());
    }

    public static function validCpfs(): array
    {
        return [
            'unmasked' => ['52998224725'],
            'masked' => ['529.982.247-25'],
        ];
    }

    public static function invalidCpfs(): array
    {
        return [
            'all repeated digits' => ['11111111111'],
            'all zeros' => ['00000000000'],
            'wrong check digits' => ['52998224700'],
            'too short' => ['529982247'],
            'too long' => ['529982247251'],
            'non numeric' => ['abc.def.ghi-jk'],
        ];
    }
}
