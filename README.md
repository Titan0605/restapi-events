# api-events

This will be an API-as-a-Service that provides events occurring within a specified date range

---

> ## ***Goals***
* Provide events based in date, budget and type
* Practice REST api structure
* Use the api for a web page
* Use a proper project structure

> ## ***Tools used***
* ### Backend
1. **Python:** Server-side programming language
2. **Xampp/MySQL:** Database management system
3. **Javascript:** Client-side scripting language
4. **Flask:** Web application framework
5. **Mapkick:** Map visualization library
6. **HTML/CSS:** Web markup and styling

* ### Development
1. **Git:** Version control system
2. **GitHub:** Code repository hosting
3. **Postman:** API testing

> ## ***Before use***
* ### Set up a Virtual Environment of python
1. **Create a virtual environment:** In the terminal, navigate to the project folder and run:

```bash
python -m venv venv
```

2. **Activate the virtual environment:**
```bash
# On Windows
venv\Scripts\activate

# On macOS/Linux
source venv/bin/activate
```

* ### Install the requirements in requirements.txt
```bash
pip install -r requirements.txt
```

* ### Create the database with the name events_db
1. **Start Xampp** and ensure MySQL service is running
2. **Create database:**
```sql
CREATE DATABASE events_db;
```
3. **Go to SQL folder in project**
4. **Import SQL file events_db.sql**
5. **Fill database with fill_db.sql**

* ### Create an account in Mapbox and get an api key
1. **Sign up** on [Mapbox](https://www.mapbox.com/)
2. **Generate an API key** from your account dashboard

* ### Create a .env file and save the api key of mapbox as: MAPBOX_TOKEN
```bash
echo "MAPBOX_TOKEN=your_mapbox_api_key_here" > .env
```

* ### Run the project
```bash
flask run
```