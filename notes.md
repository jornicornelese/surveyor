Everything starts in Analyzer->analyze($path).
Parser->parse($code);
TypeResolver tracks the scopes as new items are parsed
But scope doesn't _change_ for node resolver context?
TypeResolver enters nod eand gets a result from the corresponding class
