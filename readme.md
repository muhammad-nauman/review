So I took almost 2.5 hours to complete this task and I'm writing my insights here in details:

1. Committed the code above with refactored code above and original below to it. (Form request wasn't there so its all new.)
**~150+ lines have been reduced**

2. ### Opinion/Assessment
These are the points that I emphasis on myself during my development and they are not in the original code.

> No uses of Form Requests

It is a good way to reduce cluttered code inside controller/repository and defy the Single Responsibility Principle. Any repository method should not validate the data. if the method is to create a new entry into the database then it should just create the entry and nothing else.

> Else usage

unnecessary usage of else statements. `else` makes code look cluttered. I have refactored so many statements but there are still tons of left. `else` statements are the most hateful statement for me :smile: 

> Eloquent usage

Eloquent usage can be made better. I didn't have access to the models but scope methods are great ways to reduce the queries inside controller/repository and make code clean. Same as for accessors and mutators. For example in code, it is checking the `consumer_type` and assigning a variable its value, these 4 lines can be eliminated by using accessor/mutator. There is a difference between `get()` and `first()` methods. `get()` brings all the matched results means no limit, `first()` gets the single result. `$job->user()->get()->first();` this line will get all the users of the job and then select the first one. `$job->user()->first();` this line will get the single user which means it is faster.

> Environment Variables

`env()` returns the variables written in `.env` files, it is recommended to never use this function outside of config files. Why? On production we cache the env variables, after caching using `env()` function can result in a `null` value which can break the code. So the good practice is to define all the variables of `.env` file into a config file and then access values from them using `config()` method.

> Response pattern

For controllers handling APIs should follow the proper procedure of REST standards. `response()` can send a JSON response but it will go and run through an extra check. `response()->json()` can directly send the data as JSON and you can send relevant HTTP status codes.
