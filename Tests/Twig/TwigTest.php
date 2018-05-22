<?php
/**
 * Created by PhpStorm.
 * User: matthewthomas
 * Date: 12/11/2017
 * Time: 15:52
 */

namespace BrandOriented\Encryption\Twig;

use BrandOriented\Encryption\Bridge\Bridge;
use BrandOriented\Encryption\Encryptor\OpenSSL;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Class TwigTest
 * @package BrandOriented\Encryption\Twig
 */
class TwigTest extends TestCase
{
    /**
     * @var EncryptionExtension
     */
    private $twigExtension;

    public function setUp()
    {
        $bridge = new Bridge(new OpenSSL('key', 'AES-256-CBC', 'IV', 'SUFFIX'));

        $this->twigExtension = new EncryptionExtension($bridge);
    }

    /**
     * @dataProvider decryptProvider
     * @param string $expected
     * @param string $data
     */
    public function testDecrypt(string $expected, string $data)
    {
        $this->assertSame($expected, $this->twigExtension->decrypt($data));
    }


    /**
     * @return array
     */
    public function decryptProvider(): array
    {
        return [
            ['bob', 'TUcyS1dHQ2tFNlVtT2ZNVWw1ZzdVQT09SUFFIX'],
            ['O\'Conner', 'U3VUNHprQkZwUWF2Wno5YnJ5ZjE5Zz09SUFFIX'],
            ['R-ily', 'cmwrZFBqM2lnd25vczI3QUcxUGE5UT09SUFFIX']
        ];
    }
}
