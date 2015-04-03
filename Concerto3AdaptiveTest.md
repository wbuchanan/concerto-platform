# Concerto v3 Tutorials: Create an adaptive test #

This tutorial will show you how to create a simple adaptive test on Concerto v3.7 that looks like this:
[Concerto v3 Adaptive test](http://concerto.e-psychometrics.com/demo/?tid=80)

It is assumed that you’ve already taken the **[Concerto v3 Tutorials: Create a simple test](http://code.google.com/p/concerto-platform/wiki/Concerto3SimpleTest)** section and are familiar with the basic functions of Concerto v3.

Obviously, this is a very basic test, but it easily shows the idea behind Concerto. Don't forget to add formatting of your choice to make the test professional/pretty! The tests that you develop can be run as a separate website or embedded in the other websites or applications.

## Login to Concerto ##
It is best to use Google Chrome to run the Concerto administration panel. Please ensure that you have Concerto v3.0 or higher, preferably the latest version. Go to **concerto\_installation\_path/cms/** and login using your credentials. If you haven't installed Concerto on your server, you can use our demo installation at http://concerto.e-psychometrics.com/demo/cms/ to get a _free basic account_ or _login to an existing account_ hosted on our server.

After logging in to Concerto, switch the interface to the SIMPLE mode using the buttons in the top right corner (you will not need advanced functions as for now!):

http://concerto-platform.googlecode.com/files/simple-advanced.JPG


## Step 1: Create HTML Templates ##

### Introduction template ###

Create a new template called _introduction\_adaptive_ and add some suitable text to describe the test and make participants feel comfortable.

http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/intro.JPG


### Test item template ###
Create a new template called _test item\_adaptive_ and add the following content:
  * **{{question}}** insert  that will be filled by concerto
  * "**=**" sign
  * An input **text field** called "**response**"
    * Ensure you call this text field "**response**" as that's how we refer to it in R code. You can give it a different name, but you will have to change R code accordingly.
    * If you don't know how to add a text field, see the **Concerto v3.0 Tutorials: Create a simple test** section.
  * A button called "**Submit**"
  * For presentation purposes, add the field **{{theta}}**.

http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/items.JPG


### Feedback template ###
Create another new template called _feedback\_adaptive_ that contains:
  * A suitable title
  * A few words of empathic feedback
  * A field **{{theta}}**

http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/feedback.JPG


## Step 2: Creating an Item Bank/Table from a CSV File ##

The next step is to create an item bank for the test. Usually, software like R and LTM package are used to generate item parameters. Here, let’s use a mock-up item bank with only one item parameter (difficulty) which can be generated using the Rasch model.

Download and unzip this CSV file http://tinyurl.com/cefwg2z and explore it using Excel or notepad.

Go to the tables tab and click the ‘+ add new object’ icon to add a new table. Assign a meaningful name (E.g. adaptive\_test\_table) and save it. Click the import table from CSV file icon to import the table that you have downloaded. Concerto will automatically read the data into table format. All you need to do is alter the column names and the data types (see below).

http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/table.JPG


## Step 3: Creating a test and adding sections ##

Go to the ‘tests’ tab on the upper left side of the page. Create a new test by clicking the ‘+ add new object’ icon below the “list of available objects” (it is likely to be empty!). Assign a relevant name for the test and click ‘OK’.

To edit a test at any time, click on the pencil symbol towards the right of the specific test you want to edit. Be careful not to click the ‘bin’ icon (second symbol from the left) unless you wish to delete your test!

This step involves editing your test overall. Click the ‘tests’ tab on the upper left side of the page and click the pencil icon on your specific test to edit it. To add a new section, click the ‘+’ at the right side of the last section. At this point, the last section is ‘1. start’.

Follow the table below to add new sections to your test, using the editor. Please double-check your test logic sections using the text and screen shots provided to avoid any errors that may prevent your test from running currently! Please pay attention to all the letters, as Concerto is case sensitive. Please remember to save your test often and do NOT refresh the page or (accidentally) press ‘go back’.



| **SECTION TYPE** | **DETAILS** |
|:-----------------|:------------|
| Load HTML template | Select the **introduction** template (or whatever you named it)|
| Set variable | SET VARIABLE **theta** by R code: **"Score not available yet"**|
| Set variable | SET VARIABLE **difficulties** from table: **adaptive\_test** (or whatever you named it); COLUMNS **difficulty** |
| - | http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/1.JPG |


| **SECTION TYPE** | **DETAILS** |
|:-----------------|:------------|
| Set variable | SET VARIABLE **current\_item** by R code: **50**|
| Set variable | SET VARIABLE **responses** by R code: **NULL**|
| Set variable | SET VARIABLE **itempar** by R code: **as.matrix(cbind(1,difficulties,0,1))**|
| Set variable | SET VARIABLE **catBank** by R code: **createItemBank(itempar,model="1pl")**|
| - |  http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/2.JPG  |


| **SECTION TYPE** | **DETAILS** |
|:-----------------|:------------|
| Set variable | SET VARIABLE **items\_administered** by R code: **current\_item**|
| Set variable | SET VARIABLE **question** from table: **adaptive\_test\_table** (or whatever you named it); COLUMNS **content** WHERE **id**  _equal_  **current\_item** |
| Set variable | SET VARIABLE **difficulty** from table: **adaptive\_test\_table (or whatever you named it); COLUMNS**difficulty**WHERE**id**_equal_**current\_item|
| - |  http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/3.JPG  |


| **SECTION TYPE** | **DETAILS** |
|:-----------------|:------------|
| Set variable | SET VARIABLE **correct\_answer** from table: **adaptive\_test** (or whatever you named it); COLUMNS **correct\_answer** WHERE **id**  _equal_  **current\_item** |
| Load HTML template | Select the **test\_item\_adaptive** template (or whatever you named it).|
| IF statement | IF **is.na(response)**  _equal_  **1** THEN _GO TO:_ **(select the previous section where you load the _items_ template)**. This step is to ensure that there are no missing responses or non-numeric type responses. |
| - |  http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/4.JPG  |


| **SECTION TYPE** | **DETAILS** |
|:-----------------|:------------|
| Set variable | SET VARIABLE **responses** by R code:  **c(responses, correct\_answer == response)**   _Note: The operation **correct\_answer==response** is to compare the correct\_answer with response and give value 1 if the statement is true and 0 if false. In this way, the elements in the vector responses will be digits either 1 or 0, corresponding to right or wrong._ |
| Set variable | SET VARIABLE **theta** by R code: **it<-itempar[items\_administered,1:4, drop=F]** and in the next line **thetaEst(it, responses)**  _(Note: the above two lines of code should be in separate lines; do not enter them continuously as one sentence!)_|
| Set variable | SET VARIABLE **current\_item** by R code:  **nextItem(catBank, theta=theta, out=as.numeric(items\_administered))`[[1]]`**   _Please pay attention to all the digits and letters, as well as the brackets, since even a small typo will stop the test from running properly._|
| Set variable | SET VARIABLE **items\_administered** by R code:  **c(items\_administered, current\_item)**|
| - |  http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/5.JPG  |


| **SECTION TYPE** | **DETAILS** |
|:-----------------|:------------|
| Set variable | SET VARIABLE **nitems** by R code:  **length(items\_administered)**|
| IF statement | IF **nitems**  _equal or lesser than_  **15** THEN _GO TO_ **(select the section where you set the variable "question")** |
| Load HTML template | Select **feedback** template (or whatever else you named it) |
| - |  http://cambridgepsychometrics.com/~vaishali/v3.7/Aug08Adaptive/6.JPG  |
| - | **_END_** |


## To run your test ##

Click the **Run Test** button at the start of the tests section.

Alternatively, use the following URL:  **(concerto\_installation\_path)/?tid=(TEST\_ID)**

Or if using a free account on our server:  **http://concerto.e-psychometrics.com/demo/?tid=69**

_Substitute the last 2 digits in the URL with the_test id_of your specific test._

_Note: If the test does not work as expected, you will likely see a page with R return code. The second part down the page R output will provide you with the error message which tells you what goes wrong and in which test section._
