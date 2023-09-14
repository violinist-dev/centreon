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

namespace Core\Macro\Application\Repository;

use Core\Macro\Domain\Model\Macro;

interface WriteHostMacroRepositoryInterface
{
    /**
     * Add host's macros.
     *
     * @param Macro $macro
     *
     * @throws \Throwable
     */
    public function add(Macro $macro): void;

    /**
     * Update host's macros.
     *
     * @param Macro $macro
     *
     * @throws \Throwable
     */
    public function update(Macro $macro): void;

    /**
     * Delete host's macros.
     *
     * @param Macro $macro
     *
     * @throws \Throwable
     */
    public function delete(Macro $macro): void;
}
