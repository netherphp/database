<?php

namespace Nether\Database;
use \Nether;

use \Exception;

abstract class Coda {
/*//
co-da /ˈkōdə/
> the concluding passage of a piece or movement, typically forming an
> addition to the basic structure.

codas allow you to write some annoying or tricky things in SQL without having
to actually write the SQL to do it. the best example is proably for writing
an IN() clause, which PDO on its own has no ability to bind arrays while
binding parameters. see the Coda\In file for more detailed example.

this class provides the framework interface and some default implementation
for how codas work. each coda typically will only work upon one small addition
to a query, thusly only operating upon one field.

each coda must know at least three things before they attempt to render.

1) they must have a hold on a database connection so that they may properly
   escape data using the charset and all that jazz.
2) they must know what field you intend to operate on.
3) they must know the value or values you wish to operate with.

this data may be added to the coda at any order and at any time as long as
it knows all three things before you try and print or render it. so for example
you can write a search api method that takes codas as arguments that have only
their values yet. your method could then append the database and field name to
it afterwards.

it is up to the author of the coda to ensure what he allows you to do cannot
get you sql injected. this is why we need access to the connection, so that
anything can be manually escaped if the coda is building a complex value.
//*/

	public function
	__construct($opt=null) {
	/*//
	@argv object|array
	nothing special happens during construction. you can pass an array that
	contains a database connection, the field name, or the value. all of which
	can be specified at a later time.
	//*/

		$opt = new Nether\Object($opt,[
			'Database' => null,
			'Field'    => null,
			'Value'    => null
		]);

		$this->Database = $opt->Database;
		$this->Field = $opt->Field;
		$this->Value = $opt->Value;

		return;
	}

	public function
	__toString() {
	/*//
	@return string
	have the coda automatically build and render when you attept to use it in a
	string context.
	//*/

		return $this->Render();
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Database = null;
	/*//
	@type Nether\Database
	the db connection to use for things like escaping data.
	//*/

	public function
	GetDatabase() {
	/*//
	@return Nether\Database | null
	return the db connection or null if one has not yet been set.
	//*/

		return $this->Database;
	}

	public function
	SetDatabase(Nether\Database $DB) {
	/*//
	@argv Nether\Database
	@return self
	set a db connection for this coda.
	//*/

		$this->Database = $DB;
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Field = null;
	/*//
	@type string
	the database field that this coda is for.
	//*/

	public function
	GetField() {
	/*//
	@return string | null
	get the database field this coda is for or null if it has not yet been set.
	//*/

		return $this->Field;
	}

	public function
	SetField($Field) {
	/*//
	@argv string
	@return self
	set the field this coda is for.
	//*/

		$this->Field = $Field;
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Value = null;
	/*//
	@type mixed
	the value to use in the coda.
	//*/

	public function
	GetValue() {
	/*//
	@return string | null
	get the value to be used in this coda or null if nothing has been set yet.
	//*/

		return $this->Value;
	}

	public function
	GetSafeValue() {
	/*//
	@returns string | null
	//*/

		return $this->GetSafeInput($this->Value);
	}

	public function
	SetValue($Value) {
	/*//
	@argv mixed
	set the value to use in this coda.
	//*/

		$this->Value = $Value;
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Data = null;
	/*//
	@type mixed
	the data to use in the coda. this may influence the way the coda
	builds itself, meaning it may provide a different functionality if you
	give it an array rather than a string.
	//*/

	public function
	GetData() {
	/*//
	@return string | null
	get the data assigned to this coda - but after the coda transforms it
	for its own purposes.
	//*/

		return $this->Data;
	}

	public function
	GetSafeData() {
	/*//
	@returns tring | null
	//*/

		return $this->GetSafeInput($this->GetData());
	}

	public function
	SetData($Data) {
	/*//
	@argv mixed
	@return self
	set data for this coda to look at.
	//*/

		$this->Data = $Data;
		return $this;
	}

	public function
	ApplyData(&$Output) {
	/*//
	@argv reference
	@return self
	put the modified data into this specific reference. basically force into
	the specified reference the updated version of the data that was required
	to produce this coda. one example is, a coda may have to build a literal
	clause out of an array like in RegexLike.
	//*/


		$Output = $this->GetData();
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	IsValueBinding($Input=null) {

		if(!$Input) $Input = $this->Value;

		// is string like :this then yes
		if(is_string($Input) && strpos($Input,':') === 0)
		return true;

		// if entire list of :this then yes.
		if(is_array($Input)) {
			$All = true;
			foreach($Input as $Value) {
				if(!$this->IsValueBinding($Value))
				$All = false;
			}
			return $All;
		}

		return false;
	}

	public function
	GetSafeInput($Input) {
	/*//
	given various types of inputs, transform it to be safe for query
	injection.
	//*/

		if(is_array($Input) || is_object($Input)) {
			foreach($Input as $Key => $Value)
			$Input[$Key] = $this->Database->Escape($Value);

			return $Input;
		}

		return $this->Database->Escape($Input);
	}

	public function
	GetDataBindings() {

		// without a valid binding value skip this.
		if(!$this->IsValueBinding() && !$this->Data)
		return false;

		// if a list of bindings, return it without bothering our data.
		if(is_array($this->Value) || is_object($this->Value))
		return $this->Value;

		// return the one binding we have that has no data.
		if(is_string($this->Value) && (!$this->Data || count($this->Data) == 1))
		return [$this->Value];

		// expand the binding into a list of bindings.
		$Output = [];
		foreach(array_values($this->Data) as $Key => $Value)
		$Output[] = sprintf(
			':__%s__%d',
			trim($this->Value,':'),
			$Key
		);

		return $Output;
	}

	////////////////////////////////
	////////////////////////////////

	protected final function
	RequireDatabase() {
	/*//
	demand a database to have been set for things like rendering. i know that
	when you throw this php will be all like "tostring cant throw excptions you
	idiot" and then refuse to show you this actual error. but whatever, we need
	it to die somehow if you forgot to give it a db.
	//*/

		if(!$this->Database)
		throw new Exception("No database has been defined for this Coda.");

		return;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Render() {
	/*//
	render this coda down to its desired SQL component. this implementaiton
	will allow you to define new methods in your extension suffixed with
	the pdo driver name so that your coda may support multiple databases.
	//*/

		$this->RequireDatabase();

		// first:
		// try and use a method that is specific to the database server in
		// case the syntax varied.
		$MethodName = "Render_{$this->Database->GetDriverName()}";
		if(method_exists($this,$MethodName)) return $this->{$MethodName}();

		// then:
		// try and use a method that follows the SQL standards such that it
		// should work on almost all server types.
		$MethodName = "Render_Generic";
		if(method_exists($this,$MethodName)) return $this->{$MethodName}();

		// else:
		// fail for having no idea what to do.
		return sprintf(
			'-- Coda %s does not currently support %s (%s)',
			__CLASS__,
			$this->Database->GetDriverName(),
			$MethodName
		);
	}

}
