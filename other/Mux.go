package main

import (
	"encoding/json"
	"fmt"
	"github.com/gorilla/mux"
	"net/http"
	"github.com/go-routeros/routeros"
)

const (
	ROUTERBOARD_HOST     = "your_routerboard_host"
	ROUTERBOARD_USERNAME = "your_username"
	ROUTERBOARD_PASSWORD = "your_password"
)

func connect() (*routeros.Client, error) {
	return routeros.Dial(ROUTERBOARD_HOST, ROUTERBOARD_USERNAME, ROUTERBOARD_PASSWORD)
}

func getInterfaces(w http.ResponseWriter, r *http.Request) {
	client, err := connect()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer client.Close()

	reply, err := client.Run("/interface/print")
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(reply.Re)
}

func main() {
	r := mux.NewRouter()

	r.HandleFunc("/interfaces", get
