<?php
namespace Image\Shell;

use Cake\Console\Shell;
use Cake\FileSystem\Folder;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;

class ImageShell extends Shell {

/**
 * [getOptionParser description]
 * @return [type] [description]
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser
			->addSubcommand('regenerate', [
				'help' => 'Regenerate all presets for given Table',
			])
			->addArgument('table', [ 'help' => 'Name of the table' ]);

		return $parser;
	}

/**
 * [main description]
 * @return [type] [description]
 */
	public function main() {
		$this->regenerate();
	}

/**
 * [regenerate description]
 * @return [type] [description]
 */
	public function regenerate() {
		$table = $this->getTable();

		if (!$table->hasBehavior('Image')) {
			return $this->out('<error>Table has no ImageBehavior attached</error>');
		}

		$imagesTable = $table->imagesTable();

		$images = $imagesTable->find()
			->where(['model' => $table->alias() ]);

		$total = $images->count();
		$this->out(sprintf("<info>[PROCESSING]\t Regenerating presets for %s images</info>", $total), 0);
		$this->out('');

		$x = 1;
		foreach ($images as $image) {
			$table->generatePresets($image);
			$this->io()->overwrite(sprintf("<question>[WORKING]\t Creating presets... [%s/%s]</question>", $x, $total), 0);
			$x++;
		}

		$this->out('');
		$this->out('<success>[FINISHED]</success>');
		$this->hr();
	}


/**
 * Return Table
 * @return Cake\ORM\Table
 */
	protected function getTable() {
		if (isset($this->args[0])) {
			$table = TableRegistry::get($this->args[0]);
		} else {
			$x = 1;
			$tables = [];
			foreach ((new Folder(APP . 'Model' . DS . 'Table'))->find('.*.php') as $file) {
				$table = str_replace('Table.php', '', $file);
				$tableName = Inflector::camelize($table);
				$tableTable = TableRegistry::get($tableName);

				if ($tableTable->hasBehavior('Image')) {
					$tables[$x] = $tableTable;
					$this->out(sprintf('[%s] %s', $x, $table));
					$x++;
				}
			}

			$table = $this->in('Provide the name of the Table you want to use', null, 1);

			if (is_numeric($table) && isset($tables[$table])) {
				$table = $tables[$table];
			}
		}

		return $table;
	}

}
