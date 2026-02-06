"""
Jakarta Traffic Congestion Prediction System
Big Data Analytics with Weather Integration
"""

from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import os
import json
from scipy import stats
import warnings
warnings.filterwarnings('ignore')

app = Flask(__name__)
CORS(app)

# Data directory containing 27 CSV files
DATA_DIR = r'D:\PresUniv\Big Data Technology\Program'

# Toll gate locations with actual coordinates from OpenStreetMap

TOLL_GATES = {
'GT_Bambu_Apus_1': {'lat': -6.310022897802141, 'lng': 106.90081583697383, 'name': 'Bambu Apus 1'},
'GT_Bambu_Apus_2': {'lat': -6.307316871516078, 'lng': 106.89565657771163, 'name': 'Bambu Apus 2'},
'GT_Bintara': {'lat': -6.2218000876153505, 'lng': 106.9502088888253, 'name': 'Bintara'},
'GT_Cengkareng': {'lat': -6.105471302305508, 'lng': 106.69645950813556, 'name': 'Cengkareng'},
'GT_Cikunir_1': {'lat': -6.2532505987695775, 'lng': 106.95800185046494, 'name': 'Cikunir 1'},
'GT_Cikunir_4': {'lat': -6.256181635205935, 'lng': 106.9546954388256, 'name': 'Cikunir 4'},
'GT_Cikunir_8': {'lat': -6.257509560402211, 'lng': 106.9590000658093, 'name': 'Cikunir 8'},
'GT_Ciledug_1': {'lat': -6.238855926749509, 'lng': 106.75828429464524, 'name': 'Ciledug 1'},
'GT_Ciledug_2': {'lat': -6.233298627836666, 'lng': 106.75480486580902, 'name': 'Ciledug 2'},
'GT_Ciputat_2': {'lat': -6.276577280671806, 'lng': 106.76827253697337, 'name': 'Ciputat 2'},
'GT_Jatiasih_1': {'lat': -6.295429833789007, 'lng': 106.9554706658097, 'name': 'Jatiasih 1'},
'GT_Jatiasih_2': {'lat': -6.296609034490066, 'lng': 106.95521558115394, 'name': 'Jatiasih 2'},
'GT_Jatiwarna_1': {'lat': -6.309759933829432, 'lng': 106.92695679279346, 'name': 'Jatiwarna 1'},
'GT_Jatiwarna_2': {'lat': -6.311111370741693, 'lng': 106.92157856395744, 'name': 'Jatiwarna 2'},
'GT_Kuningan_1': {'lat': -6.233740866510915, 'lng': 106.82297290998937, 'name': 'Kuningan 1'},
'GT_Meruya_Selatan': {'lat': -6.206035940000447, 'lng': 106.73870987494978, 'name': 'Meruya Selatan'},
'GT_Meruya_Utama': {'lat': -6.192974353346789, 'lng': 106.73268339072924, 'name': 'Meruya Utama'},
'GT_Meruya_Utara': {'lat': -6.191735229178188, 'lng': 106.73344875523459, 'name': 'Meruya Utara'},
'GT_Pejompongan': {'lat': -6.206435113147771, 'lng': 106.80281239464495, 'name': 'Pejompongan'},
'GT_Pulo_Gebang': {'lat': -6.2127235191242445, 'lng': 106.95191439464492, 'name': 'Pulo Gebang'},
'GT_Semanggi_1': {'lat': -6.223496219272174, 'lng': 106.81548325046474, 'name': 'Semanggi 1'},
'GT_Semanggi_2': {'lat': -6.229727922303642, 'lng': 106.81973119279269, 'name': 'Semanggi 2'},
'GT_Senayan': {'lat': -6.214823909725501, 'lng': 106.8092057946449, 'name': 'Senayan'},
'GT_Slipi_1': {'lat': -6.199544175476062, 'lng': 106.79862788115294, 'name': 'Slipi 1'},
'GT_Tanjung_Duren': {'lat': -6.173085661620393, 'lng': 106.79038739464448, 'name': 'Tanjung Duren'},
'GT_Tebet': {'lat': -6.242407369059627, 'lng': 106.84980314882971, 'name': 'Tebet'},
'GT_Veteran_1': {'lat': -6.261212052697034, 'lng': 106.76715469485698, 'name': 'Veteran 1'}
}


class BigDataTrafficAnalyzer:
    """Big Data processing and analysis for traffic prediction"""
    
    def __init__(self):
        self.toll_data = {}
        self.baseline_stats = {}
        self.load_all_data()
        self.calculate_baseline()
    
    def load_all_data(self):
        """Load all 27 CSV files - Big Data Ingestion"""
        print("="*70)
        print("BIG DATA LOADING: Processing 27 toll gate datasets")
        print("="*70)
        
        total_records = 0
        for gate_id in TOLL_GATES.keys():
            file_path = os.path.join(DATA_DIR, f'{gate_id}.csv')
            try:
                if os.path.exists(file_path):
                    df = pd.read_csv(file_path)
                    df['datetime'] = pd.to_datetime(df['datetime'])
                    df['hour'] = df['datetime'].dt.hour
                    df['minute'] = df['datetime'].dt.minute
                    df['day_of_week'] = df['datetime'].dt.dayofweek
                    
                    self.toll_data[gate_id] = df
                    total_records += len(df)
                    print(f"✓ Loaded {gate_id}: {len(df):,} records")
                else:
                    print(f"✗ File not found: {gate_id}")
            except Exception as e:
                print(f"✗ Error loading {gate_id}: {e}")
        
        print("="*70)
        print(f"TOTAL RECORDS LOADED: {total_records:,}")
        print(f"DATASETS LOADED: {len(self.toll_data)}/27")
        print("="*70)
    
    def calculate_baseline(self):
        """Calculate baseline statistics for each toll gate - Big Data Analysis"""
        print("\nBIG DATA ANALYSIS: Calculating baseline statistics...")
        
        for gate_id, df in self.toll_data.items():
            # Hourly patterns
            hourly_stats = df.groupby('hour').agg({
                'vehicle_count': ['mean', 'std', 'min', 'max'],
                'average_speed_kmh': ['mean', 'std'],
                'congestion_level': lambda x: (x == 'High').sum() / len(x) * 100
            }).to_dict()
            
            # Overall statistics
            overall_stats = {
                'avg_vehicle_count': df['vehicle_count'].mean(),
                'std_vehicle_count': df['vehicle_count'].std(),
                'avg_speed': df['average_speed_kmh'].mean(),
                'std_speed': df['average_speed_kmh'].std(),
                'high_congestion_rate': (df['congestion_level'] == 'High').sum() / len(df) * 100,
                'medium_congestion_rate': (df['congestion_level'] == 'Medium').sum() / len(df) * 100,
                'low_congestion_rate': (df['congestion_level'] == 'Low').sum() / len(df) * 100
            }
            
            # Peak hours identification
            hourly_avg = df.groupby('hour')['vehicle_count'].mean()
            peak_hours = hourly_avg.nlargest(3).index.tolist()
            
            self.baseline_stats[gate_id] = {
                'hourly': hourly_stats,
                'overall': overall_stats,
                'peak_hours': peak_hours
            }
        
        print(f"✓ Baseline calculated for {len(self.baseline_stats)} toll gates")
    
    def predict_congestion(self, gate_id, current_hour=None, weather_condition='clear'):
        """
        Advanced congestion prediction using Big Data analytics
        
        Args:
            gate_id: Toll gate identifier
            current_hour: Hour of day (0-23)
            weather_condition: 'clear', 'rain', 'heavy_rain'
        
        Returns:
            Congestion probability (0-100%)
        """
        if gate_id not in self.toll_data:
            return 50.0  # Default fallback
        
        df = self.toll_data[gate_id]
        baseline = self.baseline_stats[gate_id]
        
        if current_hour is None:
            current_hour = datetime.now().hour
        
        # Filter data for similar time period
        hour_window = 1
        time_filtered = df[
            (df['hour'] >= current_hour - hour_window) & 
            (df['hour'] <= current_hour + hour_window)
        ]
        
        if len(time_filtered) == 0:
            time_filtered = df
        
        # === BIG DATA ANALYTICS: Multi-factor Prediction Model ===
        
        # Factor 1: Historical Pattern Analysis (40% weight)
        high_congestion_rate = (time_filtered['congestion_level'] == 'High').sum() / len(time_filtered) * 100
        medium_congestion_rate = (time_filtered['congestion_level'] == 'Medium').sum() / len(time_filtered) * 100
        historical_score = (high_congestion_rate * 1.0 + medium_congestion_rate * 0.5)
        
        # Factor 2: Vehicle Density Analysis (30% weight)
        avg_vehicles = time_filtered['vehicle_count'].mean()
        baseline_avg = baseline['overall']['avg_vehicle_count']
        baseline_std = baseline['overall']['std_vehicle_count']
        
        # Z-score normalization
        if baseline_std > 0:
            z_score = (avg_vehicles - baseline_avg) / baseline_std
            density_score = min(100, max(0, 50 + (z_score * 20)))
        else:
            density_score = 50
        
        # Factor 3: Speed Analysis (20% weight)
        avg_speed = time_filtered['average_speed_kmh'].mean()
        if avg_speed < 40:
            speed_score = 80
        elif avg_speed < 50:
            speed_score = 60
        elif avg_speed < 60:
            speed_score = 40
        else:
            speed_score = 20
        
        # Factor 4: Peak Hour Analysis (10% weight)
        is_peak = current_hour in baseline['peak_hours']
        peak_score = 75 if is_peak else 25
        
        # Weighted combination
        base_prediction = (
            historical_score * 0.40 +
            density_score * 0.30 +
            speed_score * 0.20 +
            peak_score * 0.10
        )
        
        # === WEATHER IMPACT ADJUSTMENT ===
        weather_multiplier = {
            'clear': 1.0,
            'rain': 1.25,
            'heavy_rain': 1.5,
            'cloudy': 1.1
        }
        
        final_prediction = base_prediction * weather_multiplier.get(weather_condition, 1.0)
        final_prediction = min(100, max(0, final_prediction))
        
        return round(final_prediction, 2)
    
    def analyze_route(self, gate_ids, weather_condition='clear'):
        """
        Analyze entire route with multiple toll gates
        
        Returns:
            Dictionary with overall congestion and detailed gate analysis
        """
        current_hour = datetime.now().hour
        gate_predictions = []
        total_congestion = 0
        
        for gate_id in gate_ids:
            prediction = self.predict_congestion(gate_id, current_hour, weather_condition)
            total_congestion += prediction
            
            gate_predictions.append({
                'gate_id': gate_id,
                'gate_name': TOLL_GATES[gate_id]['name'],
                'congestion_probability': prediction,
                'location': {
                    'lat': TOLL_GATES[gate_id]['lat'],
                    'lng': TOLL_GATES[gate_id]['lng']
                },
                'baseline_data': self.get_gate_summary(gate_id)
            })
        
        overall_congestion = total_congestion / len(gate_ids) if gate_ids else 0
        
        return {
            'overall_congestion': round(overall_congestion, 2),
            'gates': gate_predictions,
            'gate_count': len(gate_ids),
            'weather_condition': weather_condition,
            'prediction_time': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }
    
    def get_gate_summary(self, gate_id):
        """Get summary statistics for a toll gate"""
        if gate_id not in self.baseline_stats:
            return {}
        
        stats = self.baseline_stats[gate_id]['overall']
        return {
            'avg_vehicles': round(stats['avg_vehicle_count'], 1),
            'avg_speed': round(stats['avg_speed'], 1),
            'high_congestion_rate': round(stats['high_congestion_rate'], 1)
        }

# Initialize Big Data Analyzer
print("\n" + "="*70)
print("JAKARTA TRAFFIC PREDICTION SYSTEM - INITIALIZING")
print("="*70)
analyzer = BigDataTrafficAnalyzer()
print("\n✓ System Ready for Traffic Prediction")
print("="*70 + "\n")

def haversine_distance(lat1, lon1, lat2, lon2):
    """Calculate distance between two coordinates in km"""
    from math import radians, sin, cos, sqrt, atan2
    
    R = 6371  # Earth radius in km
    lat1, lon1, lat2, lon2 = map(radians, [lat1, lon1, lat2, lon2])
    dlat = lat2 - lat1
    dlon = lon2 - lon1
    
    a = sin(dlat/2)**2 + cos(lat1) * cos(lat2) * sin(dlon/2)**2
    c = 2 * atan2(sqrt(a), sqrt(1-a))
    
    return R * c

def find_nearest_gates(lat, lng, radius_km=5):
    """Find toll gates near a coordinate"""
    nearby = []
    for gate_id, gate_info in TOLL_GATES.items():
        dist = haversine_distance(lat, lng, gate_info['lat'], gate_info['lng'])
        if dist <= radius_km:
            nearby.append({
                'gate_id': gate_id,
                'distance': round(dist, 2),
                'name': gate_info['name']
            })
    return sorted(nearby, key=lambda x: x['distance'])

def generate_alternative_routes(start_lat, start_lng, end_lat, end_lng):
    """Generate 4 alternative routes using different strategies"""
    
    # Find gates near start and end
    start_gates = find_nearest_gates(start_lat, start_lng, radius_km=4)
    end_gates = find_nearest_gates(end_lat, end_lng, radius_km=4)
    
    # Get all gates and sort by distance to route
    all_gates_with_dist = []
    for gate_id, gate_info in TOLL_GATES.items():
        dist_to_start = haversine_distance(start_lat, start_lng, gate_info['lat'], gate_info['lng'])
        dist_to_end = haversine_distance(end_lat, end_lng, gate_info['lat'], gate_info['lng'])
        dist_to_route = min(dist_to_start, dist_to_end)
        
        all_gates_with_dist.append({
            'gate_id': gate_id,
            'dist': dist_to_route,
            'lat': gate_info['lat'],
            'lng': gate_info['lng']
        })
    
    all_gates_with_dist.sort(key=lambda x: x['dist'])
    
    routes = []
    
    # Route 1: Direct - closest gates to the route
    route1 = [g['gate_id'] for g in all_gates_with_dist[:5]]
    routes.append({'gates': route1, 'type': 'Direct', 'color': '#FF0000'})
    
    # Route 2: Northern - gates with lower latitude
    northern = sorted(all_gates_with_dist, key=lambda x: x['lat'])[:6]
    route2 = [g['gate_id'] for g in northern]
    routes.append({'gates': route2, 'type': 'Northern', 'color': '#0000FF'})
    
    # Route 3: Southern - gates with higher latitude
    southern = sorted(all_gates_with_dist, key=lambda x: -x['lat'])[:6]
    route3 = [g['gate_id'] for g in southern]
    routes.append({'gates': route3, 'type': 'Southern', 'color': '#00FF00'})
    
    # Route 4: Alternative - mixed selection
    alternative = [all_gates_with_dist[i]['gate_id'] for i in range(0, min(12, len(all_gates_with_dist)), 2)][:6]
    routes.append({'gates': alternative, 'type': 'Alternative', 'color': '#FFA500'})
    
    return routes

@app.route('/')
def index():
    """Serve main HTML page"""
    return render_template('traffic_predictor.html')

@app.route('/api/toll-gates')
def get_toll_gates():
    """Get all toll gate locations with baseline statistics"""
    gates = []
    for gate_id, gate_info in TOLL_GATES.items():
        gate_data = {
            'id': gate_id,
            'name': gate_info['name'],
            'lat': gate_info['lat'],
            'lng': gate_info['lng']
        }
        
        # Add baseline statistics if available
        if gate_id in analyzer.baseline_stats:
            gate_data['baseline'] = analyzer.get_gate_summary(gate_id)
        
        gates.append(gate_data)
    
    return jsonify(gates)

@app.route('/api/predict-routes', methods=['POST'])
def predict_routes():
    """
    Main prediction endpoint - analyzes 4 alternative routes
    Input: origin and destination coordinates, weather condition
    Output: 4 routes with congestion predictions
    """
    data = request.json
    
    origin_lat = float(data['origin_lat'])
    origin_lng = float(data['origin_lng'])
    dest_lat = float(data['dest_lat'])
    dest_lng = float(data['dest_lng'])
    weather = data.get('weather', 'clear')
    
    print(f"\n{'='*70}")
    print(f"PREDICTION REQUEST")
    print(f"{'='*70}")
    print(f"Origin: ({origin_lat}, {origin_lng})")
    print(f"Destination: ({dest_lat}, {dest_lng})")
    print(f"Weather: {weather}")
    print(f"Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    # Generate 4 alternative routes
    routes = generate_alternative_routes(origin_lat, origin_lng, dest_lat, dest_lng)
    
    # Analyze each route with Big Data
    analyzed_routes = []
    for idx, route in enumerate(routes):
        print(f"\nAnalyzing Route {idx + 1} ({route['type']})...")
        analysis = analyzer.analyze_route(route['gates'], weather)
        
        analyzed_routes.append({
            'route_id': idx + 1,
            'type': route['type'],
            'color': route['color'],
            'gates': analysis['gates'],
            'overall_congestion': analysis['overall_congestion'],
            'gate_count': analysis['gate_count'],
            'weather_condition': weather,
            'prediction_time': analysis['prediction_time']
        })
        
        print(f"  ✓ Overall Congestion: {analysis['overall_congestion']}%")
        print(f"  ✓ Gates analyzed: {analysis['gate_count']}")
    
    print(f"{'='*70}\n")
    
    return jsonify({
        'routes': analyzed_routes,
        'origin': {'lat': origin_lat, 'lng': origin_lng},
        'destination': {'lat': dest_lat, 'lng': dest_lng},
        'weather': weather,
        'timestamp': datetime.now().isoformat()
    })

@app.route('/api/gate-realtime/<gate_id>')
def get_gate_realtime(gate_id):
    """Get real-time prediction for specific toll gate"""
    weather = request.args.get('weather', 'clear')
    
    if gate_id not in TOLL_GATES:
        return jsonify({'error': 'Toll gate not found'}), 404
    
    current_hour = datetime.now().hour
    prediction = analyzer.predict_congestion(gate_id, current_hour, weather)
    
    return jsonify({
        'gate_id': gate_id,
        'name': TOLL_GATES[gate_id]['name'],
        'congestion_prediction': prediction,
        'weather': weather,
        'hour': current_hour,
        'baseline': analyzer.get_gate_summary(gate_id),
        'timestamp': datetime.now().isoformat()
    })

@app.route('/api/system-status')
def system_status():
    """Get system status and statistics"""
    total_records = sum(len(df) for df in analyzer.toll_data.values())
    
    return jsonify({
        'status': 'operational',
        'toll_gates_loaded': len(analyzer.toll_data),
        'total_records': total_records,
        'baseline_calculated': len(analyzer.baseline_stats),
        'data_directory': DATA_DIR,
        'timestamp': datetime.now().isoformat()
    })

if __name__ == '__main__':
    print("\n" + "="*70)
    print("STARTING FLASK SERVER")
    print("="*70)
    print(f"Access the application at: http://localhost:5000")
    print(f"API Endpoints:")
    print(f"  - GET  /api/toll-gates")
    print(f"  - POST /api/predict-routes")
    print(f"  - GET  /api/gate-realtime/<gate_id>")
    print(f"  - GET  /api/system-status")
    print("="*70 + "\n")
    
    app.run(debug=True, port=5000, host='0.0.0.0')
