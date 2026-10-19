<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\CSFixer;

use PHPUnit\Framework\TestCase;
use Polymorphine\Dev\CSFixer\AlignTokens;
use PhpCsFixer\Tokenizer\Tokens;


class AlignTokensTest extends TestCase
{
    public function test_ExtendedTokens()
    {
        $extended = new AlignTokens($tokens = Tokens::fromCode('<?php echo "hello";'));
        $this->assertSame($tokens->toArray(), $extended->jsonSerialize());
    }
}
