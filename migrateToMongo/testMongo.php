<html>
<body>
<pre>
<?php

// Create database hollywood. Add collection actors.
// See mongodb.txt for instructions

//Connecting to MongoDB server on localhost
// Configuration
	$dbhost = 'localhost';
	$dbname = 'hollywood';
try {
    $mongo = new Mongo("mongodb://$dbhost"); // connect
    $db = $mongo->selectDB($dbname); // select database hollywood
    // You can also use:  $db=$mongo->$dbname;
}
catch ( MongoConnectionException $e ) {
    die ('Cannot connect to mongodb');
}

// You should already have 3 actors in your collection: Richard Gere, Julia Roberts, Meryl Streep (See mongodb.tt)

// Let's add one more actor:
// db.actors.insert( { actor: "Denzel Washington",  born:1951, movies: ['The Book of Eli', 'The Manchurian Candidate', 'Safe House'] });

// Get the actors collection
$actors = $db->actors;


echo "Adding extra actor. New record created: \n";

// Let's add one more actor:
// Create new actor:
$actor = array(
	'actor' => 'Denzel Washington',
	'born' => 1951,
	'movies' => array('The Book of Eli', 'The Manchurian Candidate', 'Safe House')
);


$actors->save($actor);
// It's equivalent to:
// db.actors.insert( { actor: "Denzel Washington",  born:1951, movies: ['The Book of Eli', 'The Manchurian Candidate', 'Safe House'] });


// Find 'Denzel Washington' in the actors collection (here 'born' is not really needed because we don't have actors with the same name)
$lookup = array(
		'actor' => 'Denzel Washington',
		'born' => 1951
	);

$actor = $actors->findOne($lookup);
var_dump($actor);

//echo "\nRemoving the extra actor now! \n\n";

// removes all the actors with actor field 'Denzel Washington'
//$actors->remove(array('actor' => 'Denzel Washington'), array('justOne'=> false) );

// Above is Equivalen to :
/*
db.actors.remove(
   {actor: 'Richard Gere'},
   {
     justOne: false
   }
)
*/

echo "Checking - can we find the removed actor?: \n";

// check that we deleted it
$actor = $actors->findOne($lookup);
var_dump($actor);


// Not really needed except special cases
//$mongo->close();
?>
</pre>
</body>
</html>