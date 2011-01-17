#!/bin/sh

ls -Atrl ../lib/*.php ../*.php | tail -n 1 | awk '{print $6" "$7}'
