<?php

use PHPUnit\Framework\TestCase;

final class SystemadminRegressionTest extends TestCase
{
    public function testSendMailUsesValidatedSystemadminId()
    {
        $source = file_get_contents(__DIR__ . '/../../mvc/controllers/Systemadmin.php');
        $this->assertNotFalse($source, 'unable to read Systemadmin controller');
        $this->assertStringContainsString("'systemadminID' => \$systemadminID", $source, 'Systemadmin::send_mail should query with the validated systemadminID');
    }

    public function testDocumentUploadStoresSchoolId()
    {
        $source = file_get_contents(__DIR__ . '/../../mvc/controllers/Systemadmin.php');
        $this->assertNotFalse($source, 'unable to read Systemadmin controller');
        $this->assertStringContainsString("'schoolID' => \$this->session->userdata('schoolID')", $source, 'Systemadmin::documentUpload must persist schoolID');
    }

    public function testDeleteDocumentRemovesFromDocumentsDirectory()
    {
        $source = file_get_contents(__DIR__ . '/../../mvc/controllers/Systemadmin.php');
        $this->assertNotFalse($source, 'unable to read Systemadmin controller');
        $this->assertStringContainsString('uploads/documents/', $source, 'Systemadmin::delete_document should target the documents directory');
    }
}
