<?php

namespace App\Helpers;
use LdapRecord\Connection;

use Util;

class Ldap
{
  private $config;
  private $connection;

  public function __construct()
  {
    $this->config = array(
      'ldap_host' => env("LDAP_HOST"),
      'ldap_port' => env("LDAP_PORT"),
      'ldap_ssl' => env("LDAP_SSL"),
      'user_id_key' => env("LDAP_ACCOUNT_PREFIX"),
      'admin_id_key' => env("LDAP_ADMIN_PREFIX"),
      'admin_username' => env("LDAP_ADMIN_USERNAME"),
      'admin_password' => env("LDAP_ADMIN_PASSWORD"),
      'base_dn' => env("LDAP_BASEDN"),
      'timeout' => env("LDAP_TIMEOUT")
    );

    $this->config['account_suffix'] = implode(',', [env("LDAP_ACCOUNT_SUFFIX"), $this->config['base_dn']]);

    $this->config['ldap_url'] = $this->config['ldap_ssl'] ? 'ldaps://' : 'ldap://';
    $this->config['ldap_url'] .= $this->config['ldap_host'];
    $this->config['ldap_url'] = implode(':', [$this->config['ldap_url'], $this->config['ldap_port']]);

    $this->connection = @ldap_connect($this->config['ldap_url']);

    ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
  }

  public function verify_open_port()
  {
    return @fsockopen($this->config['ldap_host'], $this->config['ldap_port'], $errno, $errstr, $this->config['timeout']);
  }

  public function get_config()
  {
    return $this->config;
  }

  public function __get($connection)
  {
    return $this->connection;
  }

  public function bind($username, $password)
  {
    if ($this->connection) {
      return @ldap_bind($this->connection, $this->config['user_id_key'] . '=' . $username . ',' . $this->config['account_suffix'], $password);
    }
    return false;
  }

  public function bind_admin()
  {
    if ($this->connection) {
      return @ldap_bind($this->connection, $this->config['admin_id_key'] . '=' . $this->config['admin_username'] . ',' . $this->config['base_dn'], $this->config['admin_password']);
    }
    return false;
  }

  private function entry_exists($id, $type = 'id')
  {
    switch ($type) {
      case 'id':
        $identifier = 'employeeNumber';
        break;
      case 'uid':
        $identifier = 'uid';
        break;
      default:
        return null;
    }

    $search = ldap_search($this->connection, $this->config['account_suffix'], "(|(" . $identifier . "=" . $id . "))", array($this->config['user_id_key']));
    $entries = ldap_get_entries($this->connection, $search);

    if ($entries['count'] == 0) {
      return false;
    } else {
      return true;
    }
  }

  public function get_entry($id, $type = 'id')
  {
    switch ($type) {
      case 'id':
        $identifier = 'employeeNumber';
        break;
      case 'uid':
        $identifier = 'uid';
        break;
      default:
        return null;
    }

    if ($this->connection && $this->verify_open_port()) {
      if ($this->bind_admin()) {
        $search = ldap_search($this->connection, $this->config['account_suffix'], "(|(" . $identifier . "=" . $id . "))", array($this->config['user_id_key'], "title", "givenName", "cn", "sn", "mail", "employeeNumber"));
        $entries = ldap_get_entries($this->connection, $search);
        $result = [];

        foreach ($entries as $key => $value) {
          if (is_array($value) && $value[$this->config['user_id_key']]) {
            $result[] = [
              $this->config['user_id_key'] => $value[$this->config['user_id_key']][0],
              'employeeNumber' => (int)$value['employeenumber'][0],
              'givenName' => $value['givenname'][0],
              'sn' => $value['sn'][0],
              'cn' => $value['cn'][0],
              'title' => $value['title'][0],
              'dn' => $value['dn'],
              'mail' => $value['mail'][0],
            ];
          }
        }

        if (count($result) == 1) {
          return $result[0];
        } else {
          return null;
        }
      }
    }
  }

  private function valid_uid($data)
  {
    $firstname = explode(' ', $data['givenName']);
    $surnames = explode(' ', $data['sn']);

    $i = 0;
    do {
      switch ($i) {
        case 0:
          $uid = substr($firstname[0], 0, 1) . $surnames[0];
          $data["cn"] = implode(' ', [$firstname[0], $surnames[0]]);
          break;
        case 1:
          if (count($firstname) > 1) {
            $uid = substr($firstname[0], 0, 1) . substr($firstname[1], 0, 1) . $surnames[0];
            $data["cn"] = implode(' ', [$firstname[0], $firstname[1], $surnames[0]]);
          }
          break;
        case 2:
          if (count($surnames) > 1) {
            $uid = substr($firstname[0], 0, 1) . $surnames[0] . substr($surnames[1], 0, 1);
            $data["cn"] = implode(' ', [$firstname[0], $surnames[0], $surnames[1]]);
          }
          break;
        case 3:
          if (count($firstname) > 1 && count($surnames) > 1) {
            $uid = substr($firstname[0], 0, 1) . substr($firstname[1], 0, 1) . $surnames[0] . substr($surnames[1], 0, 1);
            $data["cn"] = implode(' ', [$firstname[0], $firstname[1], $surnames[0], $surnames[1]]);
          }
          break;
        default:
          $uid = substr($firstname[0], 0, 1) . $surnames[0] . ($i - 3);
          $data["cn"] = implode(' ', [$firstname[0], $surnames[0], ($i - 3)]);
      }
      $i++;
    } while ($this->entry_exists($uid, 'uid'));
    $uid = strtolower(Util::sanitize_word($uid));

    return [
      'data' => $data,
      'uid' => $uid
    ];
  }

  public function list_entries()
  {
    if ($this->connection && $this->verify_open_port()) {
      if ($this->bind_admin()) {
        $search = ldap_search($this->connection, $this->config['account_suffix'], "(|(" . $this->config['user_id_key'] . "=*))", array($this->config['user_id_key'], "title", "givenName", "cn", "sn", "mail", "employeenumber"));
        $entries = ldap_get_entries($this->connection, $search);

        $result = [];

        foreach ($entries as $key => $value) {
          if (is_array($value) && $value[$this->config['user_id_key']]) {
          //if ($value[$this->config['user_id_key']]) {
            $result[] = (object)[
              $this->config['user_id_key'] => $value[$this->config['user_id_key']][0],
              'givenName' => $value['givenname'][0],
              'employeeNumber' => (int)$value['employeenumber'][0],
              'sn' => $value['sn'][0],
              'cn' => $value['cn'][0],
              'title' => $value['title'][0],
              'dn' => $value['dn'],
              'mail' => $value['mail'][0],
            ];
          }
        }

        usort($result, function ($a, $b) {
          return $a->sn <=> $b->sn;
        });

        return $result;
      }
    }
    abort(500);
  }

  public function hash_password($password)
  {
    $salt = explode(" ", microtime())[1] * 1000000;
    for ($i = 1; $i <= 10; $i++) {
      $salt .= substr('0123456789abcdef', rand(0, 15), 1);
    }

    return "{SSHA}" . base64_encode(pack("H*", sha1($password . $salt)) . $salt);
  }

  public function unbind()
  {
    @ldap_unbind($this->connection);
    @ldap_close($this->connection);
  }
}