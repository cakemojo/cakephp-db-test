<?php

namespace DbTest\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\Utility\Hash;
use DbTest\Engine\EngineFactory;
use DbTest\TestSuite\Fixture\FixtureInjector;
use Cake\Log\Log;

class FixtureImportShell extends Shell
{

    /**
     * Import fixture shell main method
     *
     * @return void
     */
    public function main()
    {
        $this->out($this->getOptionParser()->help());
    }

    /**
     * Get & configure the option parser
     *
     * @return $this
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        return $parser->setDescription(__('DbTest fixture importer:'))
            ->addOption('dump-folder', [
                'help' => __d('DbTest', 'Provides path to dump test_db.sql file.'),
            ])->addSubcommand('dump', ['help' => __d('DbTest', 'Dumps the template database')]);
    }

    /**
     * Creates fresh dump of templates schema
     *
     * @return void
     */
    public function dump()
    {
        $skeletonDatabase = ConnectionManager::get('test_template')->config();

        if (!empty($skeletonDatabase)) {
            $skeletonName = $skeletonDatabase['database'];

            $dumpFolder = Hash::get($this->params, 'dump-folder', CONFIG . DS . 'sql');
            $this->_ensureFolder($dumpFolder);
            $dumpFile = $dumpFolder . DS . 'test_db.sql';

            Log::info("Exporting data from skeleton database: $skeletonName \n");
            $engine = EngineFactory::engine($skeletonDatabase);
            $engine->export($dumpFile, ['format' => 'plain']);
        }
    }

    /**
     * Initializes this class with a DataSource object to use as default for all fixtures
     *
     * @return void
     */
    protected function _initDb()
    {
        if ($this->_initialized) {
            return;
        }
        $db = ConnectionManager::get('test_template');
        $db->cacheSources = false;
        $this->_db = $db;
    }

    /**
     * Find and import test_skel.sql file from app/Config/sql
     *
     * @param string $path path
     * @return void
     */
    protected function _ensureFolder($path)
    {
        $Folder = new Folder($path, true);
    }
}
