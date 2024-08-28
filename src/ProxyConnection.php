<?php

declare(strict_types=1);

namespace Maximaster\BitrixSingleConnect;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Connection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\SystemException;
use CDatabase;
use CDBResult;
use RuntimeException;

/**
 * Прокси-подключение.
 */
class ProxyConnection extends CDatabase
{
    private Connection $connection;

    /**
     * @throws SystemException
     *
     * @SuppressWarnings(PHPMD.Superglobals) why:dependency
     * @SuppressWarnings(PHPMD.CamelCaseVariableName) why:dependency
     */
    public static function fromGlobal(): self
    {
        $application = Application::getInstance();
        $application->initializeBasicKernel();

        $connection = $application->getConnectionPool()->getConnection();
        if (($connection instanceof Connection) === false) {
            throw new RuntimeException('Не удалось получить данные о D7 соединении.');
        }

        global $DB;
        if (($DB instanceof CDatabase) === false) {
            throw new RuntimeException('Не удалось получить данные о соединение старого ядра.');
        }

        return new self($connection, $DB);
    }

    /**
     * @throws SystemException
     *
     * @SuppressWarnings(PHPMD.Superglobals) why:dependency
     */
    public static function replace(): void
    {
        $GLOBALS['DB'] = self::fromGlobal();
    }

    public function __construct(Connection $connection, CDatabase $oldConnection)
    {
        $this->connection = $connection;
        $this->type = 'MYSQL';
        $this->db_Conn = $oldConnection->db_Conn;
        $this->DBName = $oldConnection->DBName;
        $this->DBHost = $oldConnection->DBHost;
        $this->DBLogin = $oldConnection->DBLogin;
        $this->DBPassword = $oldConnection->DBPassword;
        $this->bConnected = $oldConnection->bConnected;
        $this->debug = $oldConnection->debug;
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritDoc}
     *
     * @param string $strSql
     * @param bool $bIgnoreErrors
     * @param string $error_position
     * @param string[] $arOptions
     *
     * @return CDBResult|false
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag) why:dependency
     * @SuppressWarnings(PHPMD.CamelCaseParameterName) why:dependency
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) why:dependency
     */
    public function Query($strSql, $bIgnoreErrors = false, $error_position = '', $arOptions = [])
    {
        try {
            return new CDBResult($this->connection->query($strSql));
        } catch (SqlQueryException $e) {
            $this->db_Error = $e->getMessage();
            $this->db_ErrorSQL = $strSql;

            return false;
        }
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}
