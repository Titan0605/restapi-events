from flask import Blueprint, jsonify, request
from app.models.eventModel import EventModel

bp = Blueprint('api', __name__, url_prefix='/api')

@bp.route('/events', methods=['GET'])
def get_events():
    events = EventModel()
    return jsonify(events.get_events())

@bp.route('/events/filter', methods=['GET'])
def get_filtered_events():
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
    events = EventModel()
    return jsonify(events.get_event_with_id(id))

@bp.route('/events/featured', methods=['GET'])
def get_featured_events():
    events = EventModel()
    return jsonify(events.get_featured_events())