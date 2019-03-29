<?php

namespace MOCUtils\Helpers;

/**
 * Class Ldap
 */
class Ldap implements JsonSerializable
{
    private $conn;
    private $connections = [];
    private $bind;
    private $configs;
    private $result;

    /**
     * Ldap constructor.
     * @param $server
     * @param int $porta
     * @throws \Exception
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (!isset($this->result[$name])) {
            throw new \Exception("Campo não encontrado na consulta.");
        }

        return $this->result[$name];
    }

    /**
     * @return mixed
     */
    public function __debugInfo()
    {
        return $this->result;
    }

    /**
     * @param $servers
     * @param $ports
     * @return Ldap
     * @throws Exception
     */
    public function setServers($servers, $ports = [])
    {
        foreach ($servers as $key => $server) {
            return $this->connect($server, isset($ports[$key]) ? $ports[$key] : 389);
        }
    }

    /**
     * @param $server
     * @param int $porta
     * @return Ldap
     * @throws Exception
     */
    public function setServer($server, $porta = 389)
    {
        return $this->connect($server, $porta);
    }

    /**
     * @param $usuario
     * @param $dominio
     * @param $pass
     * @return $this
     * @throws \Exception
     */
    public function bind($usuario, $dominio, $pass)
    {
        $this->configs['dominio'] = $dominio;
        $this->configs['usuario'] = $usuario;

        foreach ($this->connections as $conn) {
            if (@ldap_bind($conn, $usuario . "@" . $dominio, $pass)) {
                $this->conn = $conn;
                break;
            }
        }

        if (!$this->conn) {
            throw new \Exception("Usuário ou senha incorreta.");
        }

        $this->bind = true;

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function searchByUser($user)
    {
        if (!$this->bind) throw new \Exception("Bind not found.");

        $filter = "(|(samaccountname=" . $user . "))";

        if (!$result = @ldap_search($this->conn, $this->mountDn(), $filter)) {
            throw new \Exception("Não foi encontrado nenhum resultado pelo filtro.");
        }

        $entries = ldap_get_entries($this->conn, $result);
        $usuario = $entries[0];

        $this->result['name'] = isset($usuario['displayname'][0]) ? $usuario['displayname'][0] : "";
        $this->result['title'] = isset($usuario['title'][0]) ? $usuario['title'][0] : "";
        $this->result['department'] = isset($usuario['department'][0]) ? $usuario['department'][0] : "";
        $this->result['mail'] = isset($usuario['mail'][0]) ? $usuario['mail'][0] : "";

        /**
         * displayname = Nome completo
         * CN = Nome completo
         * title = Cargo
         * department = Departamento
         * userprincipalname = E-mail
         * mail = E-mail
         */

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return object
     */
    public function getResultObject()
    {
        return (object)$this->result;
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function jsonSerialize()
    {
        if (!count($this->result)) throw new \Exception("This object are empty.");

        return json_encode($this->result);
    }

    /**
     * @param $server
     * @param $porta
     * @return $this
     * @throws \Exception
     */
    private function connect($server, $porta = 389)
    {
        $conn = @ldap_connect($server, $porta);
        $this->connections[] = $conn;

        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if (!$conn) throw new \Exception("Conexão não efetuada.");

        return $this;
    }

    /**
     * @return string
     */
    private function mountDn()
    {
        $dominio = $this->configs['dominio'];

        $separetors = explode('.', $dominio);

        $ldap_base_dn = implode(",DC=", $separetors);
        $ldap_base_dn = "DC=" . $ldap_base_dn;

        return $ldap_base_dn;
    }
}
