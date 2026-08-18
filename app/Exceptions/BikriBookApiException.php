<?php

namespace App\Exceptions;

use Exception;

class BikriBookApiException extends Exception
{
 /**
 * Additional context (endpoint, payload keys, ...) kept for logs.
 *
 * @var array<string, mixed>
 */
 protected array $context;

 /**
 * @param array<string, mixed> $context
 */
 public function __construct(string $message, array $context = [], int $code = 0, ?Exception $previous = null)
 {
 parent::__construct($message, $code, $previous);
 $this->context = $context;
 }

 /**
 * @return array<string, mixed>
 */
 public function context(): array
 {
 return $this->context;
 }
}
