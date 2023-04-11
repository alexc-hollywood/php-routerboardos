from flask import Flask, request, jsonify
import routeros_api

app = Flask(__name__)

# Replace these with your Routerboard credentials
ROUTERBOARD_HOST = 'your_routerboard_host'
ROUTERBOARD_USERNAME = 'your_username'
ROUTERBOARD_PASSWORD = 'your_password'

# Connect to Routerboard OS API
def connect():
    connection = routeros_api.RouterOsApiPool(ROUTERBOARD_HOST,
                                              username=ROUTERBOARD_USERNAME,
                                              password=ROUTERBOARD_PASSWORD)
    return connection

@app.route('/interfaces', methods=['GET'])
def get_interfaces():
    try:
        with connect() as api:
            result = api.get_resource('/interface').get()
        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# Add more endpoints corresponding to Routerboard OS API methods

if __name__ == '__main__':
    app.run(debug=True)
