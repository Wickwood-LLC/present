<?php

namespace Drupal\present\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;
use Drupal\user\Entity\User;

/**
 * Converts a user code into a User entity.
 */
class UserCodeToEntityConverter implements ParamConverterInterface {

  // These are the large prime numbers used in this process.
  const A = 1000003;
  const B = 2000003;
  const M = 10000019;

  /**
   * Generate a user code for a user id.
   */
  public static function getUserCode(int $userId): int {
    return (static::A * $userId + static::B) % static::M;
  }

  public static function modInverse($a, $m) {
    $m0 = $m;
    $y = 0;
    $x = 1;

    if ($m == 1) return 0;

    while ($a > 1) {
      // q is quotient
      $q = floor($a / $m);
      $t = $m;

      // m is remainder now, process same as Euclid's algorithm
      $m = $a % $m;
      $a = $t;
      $t = $y;

      $y = $x - $q * $y;
      $x = $t;
    }

    // Make x positive
    if ($x < 0) {
      $x += $m0;
    }

    return $x;
  }

  /**
   * This does reverse of getUserCode().
   */
  function getUserID(int $user_code): int {
    // Calculate modular inverse of a
    $aInverse = static::modInverse(static::A, static::M);
    
    // Apply the inverse function
    return ($aInverse * ($user_code - static::B + static::M)) % static::M; // +m to ensure positive value
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if ($value) {
      // Attempt to load the User entity using the provided user ID.
      $uid = static::getUserID($value);
    }
    else {
      $uid = 0;
    }
    $user = User::load($uid);

    // Return the User entity or NULL if not found.
    return $user ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    // Check if the parameter is of type 'user_code'.
    return isset($definition['type']) && $definition['type'] === 'user_code';
  }
}
