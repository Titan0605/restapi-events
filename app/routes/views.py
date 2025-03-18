from flask import Blueprint, render_template, jsonify, current_app
from datetime import datetime
from mapkick.flask import Map
import requests

bp = Blueprint('views', __name__)

@bp.route('/')
def index():
    """ Index route where is the Home page """
    api_get_event = "http://127.0.0.1:4000/api/events/featured"

    try:
        response = requests.get(api_get_event)
        response.raise_for_status()

        events = response.json()

        for event in events:
            if isinstance(event["date"], str):
                event["date"] = datetime.strptime(event["date"], "%a, %d %b %Y %H:%M:%S %Z")

        return render_template('index.html', events=events)
    
    except requests.exceptions.RequestException as e:
        return internal_server_error(e)
    
@bp.route('/events')
def show_events():
    """ Events route where user can see all the events available """
    api_get_event = "http://127.0.0.1:4000/api/events"
    
    try:
        response = requests.get(api_get_event)
        response.raise_for_status()

        events = response.json()

        for event in events:
            if isinstance(event["date"], str):
                event["date"] = datetime.strptime(event["date"], "%a, %d %b %Y %H:%M:%S %Z")

        return render_template('events.html', events=events)
    
    except requests.exceptions.RequestException as e:
        return internal_server_error(e)

@bp.route('/event/<int:id>')
def show_event(id):
    """ Event route where user can see the details of an event """
    api_get_event = f"http://127.0.0.1:4000/api/events/{id}"

    try:
        response = requests.get(api_get_event)
        response.raise_for_status()

        event = response.json()

        if isinstance(event["date"], str):
                event["date"] = datetime.strptime(event["date"], "%a, %d %b %Y %H:%M:%S %Z")

        map = Map([{'latitude': event["location"]["lat"], 'longitude': event["location"]["lng"], 'label': event["location"]["address"], 'tooltip': event["name"]}], zoom=14, controls=True, markers={'color': '#348774'})

        return render_template('event_detail.html', event=event, map=map, mapbox_token=current_app.config["MAPBOX_ACCESS_TOKEN"])
    
    except requests.exceptions.RequestException as e:
        return page_not_found(e)
    
@bp.route('/organizers')
def get_organizers():
    """ Route to get all organizers """
    api_get_event = "http://127.0.0.1:4000/api/organizers"
    
    try:
        response = requests.get(api_get_event)
        response.raise_for_status()

        organizers = response.json()
        return render_template('organizers.html', organizers=organizers)
    
    except requests.exceptions.RequestException as e:
        return internal_server_error(e)

@bp.route('/organizer/<int:id>')
def get_organizer_events(id):
    """ Event route where user can see all the events of an organizer """
    api_get_event = f"http://127.0.0.1:4000/api/organizer/{id}"

    try:
        response = requests.get(api_get_event)
        response.raise_for_status()

        events = response.json()

        for event in events:
            if isinstance(event["date"], str):
                event["date"] = datetime.strptime(event["date"], "%a, %d %b %Y %H:%M:%S %Z")

        return render_template('organizer_events.html', events=events, event=events[0])

    except requests.exceptions.RequestException as e:
        return page_not_found(e)

@bp.app_errorhandler(404)
def page_not_found(error):
    """ Error handler to redirect the page 404 """
    return render_template('error/404.html', error=error), 404

@bp.app_errorhandler(500)
def internal_server_error(error):
    """ Error handler to redirect the page 500 """
    return render_template('error/500.html', error=error), 500