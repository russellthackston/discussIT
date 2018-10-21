<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;


final class RegistrationCodeTest extends TestCase {

	/*
		Test class definition
	*/	
	public function testClassDefinition() : void {
		$regcode = new RegistrationCode("code","start","end");
		$this->assertTrue(
			method_exists($regcode, 'fromArray'), 
			'Class does not have method fromArray'
		);
		$this->assertTrue(
			method_exists($regcode, 'listFromArray'), 
			'Class does not have method listFromArray'
		);
	}

	/*
		Test constructor
	*/	
	public function testConstructor() : void {
		
		$regcode = new RegistrationCode("code","start","end");
		$this->assertEquals('code', $regcode->registrationcode);
		$this->assertEquals('start', $regcode->starttime);
		$this->assertEquals('end', $regcode->endtime);

	}

	public function testConstructorEmptyRegCode() : void {
		$this->expectException(Exception::class);
		new RegistrationCode(null,"start","end");
	}

	public function testConstructorEmptyStart() : void {
		$this->expectException(Exception::class);
		new RegistrationCode("code",null,"end");
	}

	public function testConstructorEmptyEnd() : void {
		$this->expectException(Exception::class);
		new RegistrationCode("code","start",null);
	}

	/*
		Test fromArray()
	*/	
	public function testFromArray() : void {
		
		$regcode = RegistrationCode::fromArray(["registrationcode"=>"code","starttime"=>"start","endtime"=>"end"]);
		$this->assertEquals('code', $regcode->registrationcode);
		$this->assertEquals('start', $regcode->starttime);
		$this->assertEquals('end', $regcode->endtime);

	}

	public function testFromArrayEmptyRegCode() : void {
		$this->expectException(Exception::class);
		new RegistrationCode(null,"start","end");
	}

	public function testFromArrayEmptyStart() : void {
		$this->expectException(Exception::class);
		new RegistrationCode("code",null,"end");
	}

	public function testFromArrayEmptyEnd() : void {
		$this->expectException(Exception::class);
		new RegistrationCode("code","start",null);
	}

	/*
		Test listFromArray()
	*/	
	public function testListFromArray() : void {
		
		$regcodes = RegistrationCode::listFromArray([
			["registrationcode"=>"code1","starttime"=>"start1","endtime"=>"end1"],
			["registrationcode"=>"code2","starttime"=>"start2","endtime"=>"end2"]
		]);
		$this->assertEquals(2, sizeof($regcodes));

		$this->assertEquals('code1', $regcodes[0]->registrationcode);
		$this->assertEquals('start1', $regcodes[0]->starttime);
		$this->assertEquals('end1', $regcodes[0]->endtime);

		$this->assertEquals('code2', $regcodes[1]->registrationcode);
		$this->assertEquals('start2', $regcodes[1]->starttime);
		$this->assertEquals('end2', $regcodes[1]->endtime);

	}

	public function testListFromArrayEmptyRegCode() : void {
		$this->expectException(Exception::class);
		$regcodes = RegistrationCode::listFromArray([
			["registrationcode"=>"code1",null=>"start1","endtime"=>"end1"]
		]);
	}

	public function testListFromArrayEmptyStart() : void {
		$this->expectException(Exception::class);
		$regcodes = RegistrationCode::listFromArray([
			["registrationcode"=>"code1","starttime"=>null,"endtime"=>"end1"]
		]);
	}

	public function testListFromArrayEmptyEnd() : void {
		$this->expectException(Exception::class);
		$regcodes = RegistrationCode::listFromArray([
			["registrationcode"=>"code1","starttime"=>"start1","endtime"=>null]
		]);
	}

	/*
		Test setter and getters
	*/
	public function testSettersAndGetters() : void {
		
		$regcode = new RegistrationCode("code","start","end");
		$regcode->registrationcode = "code1";
		$regcode->starttime = "start1";
		$regcode->endtime = "end1";
		$this->assertEquals('code1', $regcode->registrationcode);
		$this->assertEquals('start1', $regcode->starttime);
		$this->assertEquals('end1', $regcode->endtime);

	}
	
	/*
		Test justCodes()
	*/
	public function testJustCodes() : void {
		$regcodes = RegistrationCode::listFromArray([
			["registrationcode"=>"code1","starttime"=>"start1","endtime"=>"end1"],
			["registrationcode"=>"code2","starttime"=>"start2","endtime"=>"end2"],
			["registrationcode"=>"code3","starttime"=>"start3","endtime"=>"end3"]
		]);
		$codes = RegistrationCode::justCodes($regcodes);
		$this->assertEquals(3, sizeof($codes));
		$this->assertEquals("code1", $codes[0]);
		$this->assertEquals("code2", $codes[1]);
		$this->assertEquals("code3", $codes[2]);
		
	}

}

?>