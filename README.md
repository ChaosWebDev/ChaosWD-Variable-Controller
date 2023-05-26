Install:

```
composer require chaoswd/variable-controller
```

Instantiate and Run (It is static, so you don't need to instantiate it with 'new'):

```
$rootDirectory = "/../"; //Should be the highest level directory (typically 1 above public)
VariableController::process($rootDirectory);
```

File Types That Are Searched For:

<ul>
    <li>.env</li>
    <li>.ini</li>
    <li>.conf</li>
</ul>

What It Does:<br>

1. The system searches for any files that match the above types.<br>
2. The system turns any variables found in said files into $\_ENV variables for access.<br>
3. a. If a duplicate key is found, it will add an integer (incrementing) to the end of the variable name, such as "username1", "username2", etc.<br>
