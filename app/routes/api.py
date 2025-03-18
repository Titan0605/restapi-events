from flask import Blueprint, jsonify, request, abort
from app.models.eventModel import EventModel

bp = Blueprint('api', __name__, url_prefix='/api')

@bp.route('/events', methods=['GET'])
def get_events():
    """ Endpoint to get all the events in JSON format """
    events = EventModel()
    return jsonify(events.get_events())

@bp.route('/events/filter', methods=['GET'])
def get_filtered_events():
    """ Endpoint to filter the events by parameters give it by arguments """
    events = EventModel()
    try:
        start_date = request.args.get('fechaInicio')
        end_date = request.args.get('fechaFin')
        budget = request.args.get('presupuesto')
        type_id = request.args.getlist('tipo')
        match (budget, type_id):
            case ('', []):
                return jsonify(events.get_filtered_events(start_date, end_date))
            case ('', type_id):
                return jsonify(events.get_filtered_events(start_date, end_date, type_id))
            case (budget, []):
                return jsonify(events.get_filtered_events(start_date, end_date, type_id=None, budget=budget))
            case _:
                return jsonify(events.get_filtered_events(start_date, end_date, type_id, budget))
    except KeyError as e:
        return jsonify({'error': str(e)})

@bp.route('/events/<int:id>', methods=['GET'])
def get_event(id):
    """ Endpoint to get the information of only one event """
    events = EventModel()
    event = events.get_event_with_id(id)

    if event is None:
        abort(404)

    return jsonify(event)

@bp.route('/events/featured', methods=['GET'])
def get_featured_events():
    """ Endpoint to get the events of the week """
    events = EventModel()
    return jsonify(events.get_featured_events())

@bp.route('/organizers', methods=['GET'])
def get_organizers():
    """ Endpoint to get all the organizers """
    events = EventModel()
    return jsonify(events.get_organizers())

@bp.route('/organizer/<int:id>', methods=['GET'])
def get_organizer_events(id):
    """ Endpoint to get all the events of an organizer """
    event = EventModel()
    organizer_events = event.get_organizer_events(id)
    if not organizer_events:
        abort(404)
    return jsonify(organizer_events)