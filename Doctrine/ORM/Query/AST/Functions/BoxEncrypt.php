<?php
declare(strict_types=0);
/*
 * This file is part of the BrandOriented package.
 *
 * (c) Brand Oriented sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Dominik Labudzinski <dominik@labudzinski.com>
 * @name BoxEncrypt.php - 23-05-2018 10:46
 */

namespace BrandOriented\Encryption\Doctrine\ORM\Query\AST\Functions;


/**
 * Class BoxEncrypt
 * @package BrandOriented
 */
class BoxEncrypt extends AbstractFunction
{
    protected function customiseFunction()
    {
        $this->setFunctionPrototype("encode(crypto_secretbox(decode(%s, 'base64'), CONCAT(id, %s)::bytea, %s), 'base64')");
        $this->addLiteralMapping('StringPrimary');
        $this->addLiteralMapping('StringPrimary');
        $this->addLiteralMapping('StringPrimary');
    }
}