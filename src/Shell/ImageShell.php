<?php
namespace Image\Shell;

use Cake\Console\Shell;
use Cake\FileSystem\Folder;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;

/**
 * ImagesShell
 */
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
			]);

		return $parser;
	}

/**
 * [main description]
 * @return [type] [description]
 */
	public function main() {
		$this->regenerate();
	}

	public function regenerate() {
		$this->out('<info>Select the table you want to regenerate image presets for</info>');
		$table = $this->getTable();

		if (is_array($table)) {
			foreach ($table as $tableTable) {
				$this->_regenerate($tableTable);
			}
		} else {
			$this->_regenerate($table);
		}
	}

/**
 * [regenerate description]
 * @return [type] [description]
 */
	public function _regenerate($table) {
		$alias = $table->alias();
		$imagesTable = $table->imagesTable();
		$images = $imagesTable->find()
			->where(['model' => $table->alias() ]);

		$total = $images->count();
		$this->out(sprintf("<info>[%s]\t Regenerating presets for %s images</info>", $alias, $total), 0);
		$this->out('');

		$x = 1;
		foreach ($images as $image) {
			$table->generatePresets($image, true);
			$this->io()->overwrite(sprintf("<question>[%s]\t Creating presets... [%s/%s]</question>", $alias, $x, $total), 0);
			$x++;
		}

		$this->out('');
		$this->out(sprintf("<success>[%s]\t FINISHED</success>", $alias));
		$this->hr();
	}


/**
 * Return Table
 * @return Cake\ORM\Table
 */
	protected function getTable() {
		$tables = $this->getTables();

		$options = [ 1 => 'All tables' ];
		foreach ($tables as $tableName => $table) {
			$options[] = $tableName;
		}

		foreach ($options as $option => $name) {
			$this->out(sprintf('[%s] %s', $option, $name));
		}

		$selection = $this->in('Provide the name of the Table you want to use', null, 1);

		if (isset($options[$selection]) && isset($tables[$options[$selection]])) {
			return $tables[$options[$selection]];
		}

		return $tables;
	}

/**
 * Return list of all tables where the imagebehavior is attached to.
 * @return array
 */
	protected function getTables() {
		$tables = [];
		foreach ((new Folder(APP . 'Model' . DS . 'Table'))->find('.*.php') as $file) {
			$table = str_replace('Table.php', '', $file);
			$tableName = Inflector::camelize($table);
			$tableTable = TableRegistry::get($tableName);

			if ($tableTable->hasBehavior('Image')) {
				$tables[$tableName] = $tableTable;
			}
		}

		return $tables;
	}

}
