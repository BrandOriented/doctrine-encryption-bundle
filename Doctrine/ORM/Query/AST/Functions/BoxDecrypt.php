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
 * @name BoxDecrypt.php - 23-05-2018 10:46
 */

namespace BrandOriented\Encryption\Doctrine\ORM\Query\AST\Functions;


/**
 * Class BoxDecrypt
 * @package BrandOriented
 */
class BoxDecrypt extends AbstractFunction
{
    protected function customiseFunction()
    {
        $this->setFunctionPrototype("crypto_secretbox_open(%s, CONCAT(id, %s)::bytea, %s)");
        $this->addLiteralMapping('StringPrimary');
        $this->addLiteralMapping('StringPrimary');
        $this->addLiteralMapping('StringPrimary');
    }
}