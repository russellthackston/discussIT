<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;


final class NoteTest extends TestCase {

	/*
		Test class definition
	*/	
	public function testClassDefinition() : void {
		$note = new Note("id","text","order","regcode");
		$this->assertTrue(
			method_exists($note, 'fromArray'), 
			'Class does not have method fromArray'
		);
		$this->assertTrue(
			method_exists($note, 'listFromArray'), 
			'Class does not have method listFromArray'
		);
	}

	/*
		Test constructor
	*/	
	public function testConstructor() : void {
		
		$note = new Note("id","text","order","regcode");
		$this->assertEquals('id', $note->id);
		$this->assertEquals('text', $note->text);
		$this->assertEquals('order', $note->order);
		$this->assertEquals('regcode', $note->registrationcode);

	}

	public function testConstructorEmptyID() : void {
		$this->expectException(Exception::class);
		new Note(null,"text","order","regcode");
	}

	public function testConstructorEmptyText() : void {
		$this->expectException(Exception::class);
		new Note("id",null,"order","regcode");
	}

	public function testConstructorEmptyOrder() : void {
		$this->expectException(Exception::class);
		new Note("id","text",null,"regcode");
	}

	public function testConstructorEmptRegCode() : void {
		$this->expectException(Exception::class);
		new Note("id","text","order",null);
	}

	/*
		Test fromArray()
	*/	
	public function testFromArray() : void {
		
		$note = Note::fromArray(["noteid"=>"id","notetext"=>"text","noteorder"=>"order","registrationcode"=>"regcode"]);
		$this->assertEquals('id', $note->id);
		$this->assertEquals('text', $note->text);
		$this->assertEquals('order', $note->order);
		$this->assertEquals('regcode', $note->registrationcode);
	}

	public function testFromArrayEmptyID() : void {
		$this->expectException(Exception::class);
		new Note(null,"text","order","regcode");
	}

	public function testFromArrayEmptyText() : void {
		$this->expectException(Exception::class);
		new Note("id",null,"order","regcode");
	}

	public function testFromArrayEmptyOrder() : void {
		$this->expectException(Exception::class);
		new Note("id","text",null,"regcode");
	}

	public function testFromArrayEmptyRegCode() : void {
		$this->expectException(Exception::class);
		new Note("id","text","order",null);
	}

	/*
		Test listFromArray()
	*/	
	public function testListFromArray() : void {
		
		$notes = Note::listFromArray([
			["noteid"=>"id1","notetext"=>"text1","noteorder"=>"order1","registrationcode"=>"regcode1"],
			["noteid"=>"id2","notetext"=>"text2","noteorder"=>"order2","registrationcode"=>"regcode2"]
		]);
		$this->assertEquals(2, sizeof($notes));

		$this->assertEquals('id1', $notes[0]->id);
		$this->assertEquals('text1', $notes[0]->text);
		$this->assertEquals('order1', $notes[0]->order);
		$this->assertEquals('regcode1', $notes[0]->registrationcode);

		$this->assertEquals('id2', $notes[1]->id);
		$this->assertEquals('text2', $notes[1]->text);
		$this->assertEquals('order2', $notes[1]->order);
		$this->assertEquals('regcode2', $notes[1]->registrationcode);

	}

	public function testListFromArrayEmptyID() : void {
		$this->expectException(Exception::class);
		$notes = Note::listFromArray([
			["noteid"=>null,"notetext"=>"text2","noteorder"=>"order2","registrationcode"=>"regcode1"]
		]);
	}

	public function testListFromArrayEmptyText() : void {
		$this->expectException(Exception::class);
		$notes = Note::listFromArray([
			["noteid"=>"id2","notetext"=>null,"noteorder"=>"order2","registrationcode"=>"regcode1"]
		]);
	}

	public function testListFromArrayEmptyOrder() : void {
		$this->expectException(Exception::class);
		$notes = Note::listFromArray([
			["noteid"=>"id2","notetext"=>"text2","noteorder"=>null,"registrationcode"=>"regcode1"]
		]);
	}

	public function testListFromArrayEmptyRegCode() : void {
		$this->expectException(Exception::class);
		$notes = Note::listFromArray([
			["noteid"=>"id2","notetext"=>"text2","noteorder"=>"order2","registrationcode"=>null]
		]);
	}

	/*
		Test setter and getters
	*/
	public function testSettersAndGetters() : void {
		
		$note = new Note("id","text","order","regcode");
		$note->id = "id";
		$note->text = "text";
		$note->order = "order";
		$note->registrationcode = "registrationcode";
		$this->assertEquals('id', $note->id);
		$this->assertEquals('text', $note->text);
		$this->assertEquals('order', $note->order);
		$this->assertEquals('registrationcode', $note->registrationcode);

	}

}

?>