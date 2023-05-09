<?php

/*
 * Copyright 2005 - 2023 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Tests\Core\HostSeverity\Domain\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\HostSeverity\Domain\Model\HostSeverity;

beforeEach(function (): void {
    $this->severityName = 'host-name';
    $this->severityAlias = 'host-alias';
    $this->level = 1;
    $this->iconId = 2;
});

it('should return properly set host severity instance', function (): void {
    $hostSeverity = new HostSeverity(1, $this->severityName, $this->severityAlias, $this->level, $this->iconId);

    expect($hostSeverity->getId())->toBe(1)
        ->and($hostSeverity->getName())->toBe($this->severityName)
        ->and($hostSeverity->getAlias())->toBe($this->severityAlias);
});

it('should trim the fields "name" and "alias"', function (): void {
    $hostSeverity = new HostSeverity(
        1,
        $nameWithSpaces = '  my-name  ',
        $aliasWithSpaces = '  my-alias  ',
        $this->level,
        $this->iconId
    );

    expect($hostSeverity->getName())->toBe(trim($nameWithSpaces))
        ->and($hostSeverity->getAlias())->toBe(trim($aliasWithSpaces));
});

it('should throw an exception when host severity name is empty', function (): void {
    new HostSeverity(1, '', $this->severityAlias, $this->level, $this->iconId);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::notEmptyString('HostSeverity::name')
        ->getMessage()
);

it('should throw an exception when host severity name is too long', function (): void {
    new HostSeverity(1, str_repeat('a', HostSeverity::MAX_NAME_LENGTH + 1), $this->severityAlias, $this->level, $this->iconId);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', HostSeverity::MAX_NAME_LENGTH + 1),
        HostSeverity::MAX_NAME_LENGTH + 1,
        HostSeverity::MAX_NAME_LENGTH,
        'HostSeverity::name'
    )->getMessage()
);

it('should throw an exception when host severity alias is empty', function (): void {
    new HostSeverity(1, $this->severityName, '', $this->level, $this->iconId);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::notEmptyString('HostSeverity::alias')
        ->getMessage()
);

it('should throw an exception when host severity alias is too long', function (): void {
    new HostSeverity(1, $this->severityName, str_repeat('a', HostSeverity::MAX_ALIAS_LENGTH + 1), $this->level, $this->iconId);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', HostSeverity::MAX_ALIAS_LENGTH + 1),
        HostSeverity::MAX_ALIAS_LENGTH + 1,
        HostSeverity::MAX_ALIAS_LENGTH,
        'HostSeverity::alias'
    )->getMessage()
);

it('should throw an exception when host severity comment is too long', function (): void {
    $hostSeverity = new HostSeverity(1, $this->severityName, $this->severityAlias, $this->level, $this->iconId);
    $hostSeverity->setComment(str_repeat('a', HostSeverity::MAX_COMMENT_LENGTH + 1));
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', HostSeverity::MAX_COMMENT_LENGTH + 1),
        HostSeverity::MAX_COMMENT_LENGTH + 1,
        HostSeverity::MAX_COMMENT_LENGTH,
        'HostSeverity::comment'
    )->getMessage()
);

it('should throw an exception when host severity level is too high', function (): void {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        HostSeverity::MAX_LEVEL_VALUE + 1,
        $this->iconId
    );
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::max(HostSeverity::MAX_LEVEL_VALUE + 1, HostSeverity::MAX_LEVEL_VALUE, 'HostSeverity::level')
        ->getMessage()
);

it('should throw an exception when host severity level is too low', function (): void {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        HostSeverity::MIN_LEVEL_VALUE - 1,
        $this->iconId
    );
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::min(HostSeverity::MIN_LEVEL_VALUE - 1, HostSeverity::MIN_LEVEL_VALUE, 'HostSeverity::level')
        ->getMessage()
);

it('should throw an exception when host severity name is set empty', function () {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        $this->level,
        $this->iconId
    );
    $hostSeverity->setName('');
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::notEmptyString('HostSeverity::name')
        ->getMessage()
);

it('should throw an exception when host severity name is set too long', function () {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        $this->level,
        $this->iconId
    );
    $hostSeverity->setName(str_repeat('a', HostSeverity::MAX_NAME_LENGTH + 1));
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', HostSeverity::MAX_NAME_LENGTH + 1),
        HostSeverity::MAX_NAME_LENGTH + 1,
        HostSeverity::MAX_NAME_LENGTH,
        'HostSeverity::name'
    )->getMessage()
);

it('should throw an exception when host severity alias is set empty', function () {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        $this->level,
        $this->iconId
    );
    $hostSeverity->setAlias('');
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::notEmptyString('HostSeverity::alias')
        ->getMessage()
);

it('should throw an exception when host severity alias is set too long', function () {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        $this->level,
        $this->iconId
    );
    $hostSeverity->setAlias(str_repeat('a', HostSeverity::MAX_ALIAS_LENGTH + 1));
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', HostSeverity::MAX_ALIAS_LENGTH + 1),
        HostSeverity::MAX_ALIAS_LENGTH + 1,
        HostSeverity::MAX_ALIAS_LENGTH,
        'HostSeverity::alias'
    )->getMessage()
);

it('should throw an exception when host severity level is set too high', function () {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        $this->level,
        $this->iconId
    );
    $hostSeverity->setLevel(HostSeverity::MAX_LEVEL_VALUE + 1);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::max(HostSeverity::MAX_LEVEL_VALUE + 1, HostSeverity::MAX_LEVEL_VALUE, 'HostSeverity::level')
        ->getMessage()
);

it('should throw an exception when host severity level is set too low', function () {
    $hostSeverity = new HostSeverity(
        1,
        $this->severityName,
        $this->severityAlias,
        $this->level,
        $this->iconId
    );
    $hostSeverity->setLevel(HostSeverity::MIN_LEVEL_VALUE - 1);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::min(HostSeverity::MIN_LEVEL_VALUE - 1, HostSeverity::MIN_LEVEL_VALUE, 'HostSeverity::level')
        ->getMessage()
);
