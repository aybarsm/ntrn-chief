<?php

namespace Illuminate\Database\Migrations;

interface MigrationRepositoryInterface
{





public function getRan();







public function getMigrations($steps);







public function getMigrationsByBatch($batch);






public function getLast();






public function getMigrationBatches();








public function log($file, $batch);







public function delete($migration);






public function getNextBatchNumber();






public function createRepository();






public function repositoryExists();






public function deleteRepository();







public function setSource($name);
}
